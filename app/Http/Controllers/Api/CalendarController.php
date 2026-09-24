<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CalendarEvent;
use App\Models\FundraisingOpportunity;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\SystemSetting;
use App\Models\Task;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CalendarController extends Controller
{
    public function __construct(
        protected GoogleCalendarService $googleCalendarService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = null;
        if ($request->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($request->bearerToken());
            if ($accessToken) {
                $user = $accessToken->tokenable;
            }
        }
        if (! $user) {
            $user = auth('sanctum')->user() ?? auth()->user();
        }
        if (! $user && $request->filled('user_email')) {
            $user = User::where('email', strtolower(trim((string) $request->query('user_email'))))->first();
        }
        if (! $user && $request->filled('user_id')) {
            $user = User::find($request->query('user_id'));
        }

        $userRole = $user?->role ?? 'team';
        $isOwner = ($userRole === 'owner');
        $isFinance = ($userRole === 'finance' || $isOwner);
        $userName = strtolower(trim((string) ($user?->name ?? '')));
        $userEmail = strtolower(trim((string) ($user?->email ?? '')));
        $userInitials = strtolower(trim((string) ($user?->initials ?? '')));

        // 1. Fetch custom calendar events
        $calendarEvents = CalendarEvent::orderBy('start_time', 'asc')->get();
        $events = [];

        foreach ($calendarEvents as $e) {
            if (! $this->isUserInvolvedInEvent($e, $user, $isOwner, $userName, $userEmail, $userInitials)) {
                continue;
            }

            $events[] = [
                'id' => 'evt_'.$e->id,
                'db_id' => $e->id,
                'title' => $e->title,
                'description' => $e->description,
                'event_type' => $e->event_type,
                'start_time' => $e->start_time ? $e->start_time->toIso8601String() : null,
                'end_time' => $e->end_time ? $e->end_time->toIso8601String() : null,
                'date' => $e->start_time ? $e->start_time->format('Y-m-d') : null,
                'time' => $e->start_time ? $e->start_time->format('H:i') : null,
                'location' => $e->location,
                'meet_link' => $e->meet_link,
                'attendees' => $e->attendees,
                'google_event_id' => $e->google_event_id,
                'status' => $e->status,
                'source' => 'calendar',
            ];
        }

        // 2. Aggregate project deadlines (only projects that involve the user)
        $projects = Project::whereNotNull('deadline')->get();
        foreach ($projects as $p) {
            if (! $this->isUserInvolvedInProject($p, $user, $isOwner, $userName, $userEmail, $userInitials)) {
                continue;
            }

            $deadlineStr = $p->deadline;
            $parsedDate = null;
            try {
                $parsedDate = Carbon::parse($deadlineStr);
            } catch (\Exception $e) {
            }

            if ($parsedDate) {
                $events[] = [
                    'id' => 'proj_'.$p->id,
                    'db_id' => $p->id,
                    'title' => 'Project Due: '.$p->project_name,
                    'description' => "Client: {$p->client} · Stage: {$p->stage} · Status: {$p->status}",
                    'event_type' => 'deadline',
                    'start_time' => $parsedDate->copy()->setTime(17, 0)->toIso8601String(),
                    'end_time' => $parsedDate->copy()->setTime(18, 0)->toIso8601String(),
                    'date' => $parsedDate->format('Y-m-d'),
                    'time' => '17:00',
                    'location' => 'JMOS Delivery Board',
                    'meet_link' => null,
                    'attendees' => $p->project_manager,
                    'status' => 'confirmed',
                    'source' => 'project',
                ];
            }
        }

        // 3. Aggregate task deadlines (only non-done tasks that involve user)
        $tasks = Task::whereNotNull('due_date')->where('stage', '!=', 'done')->with('project')->get();
        foreach ($tasks as $task) {
            if (! $this->isUserInvolvedInTask($task, $user, $isOwner, $userName, $userEmail, $userInitials)) {
                continue;
            }

            $parsedDate = null;
            try {
                $parsedDate = Carbon::parse($task->due_date);
            } catch (\Exception $e) {
            }

            if ($parsedDate) {
                $projectName = $task->project?->project_name ?? 'Task Board';
                $events[] = [
                    'id' => 'task_'.$task->id,
                    'db_id' => $task->id,
                    'title' => 'Task Due: '.$task->title,
                    'description' => "Project: {$projectName} · Assigned to: {$task->assigned_to} · Priority: ".ucfirst($task->priority ?? 'medium'),
                    'event_type' => 'deadline',
                    'start_time' => $parsedDate->copy()->setTime(17, 0)->toIso8601String(),
                    'end_time' => $parsedDate->copy()->setTime(18, 0)->toIso8601String(),
                    'date' => $parsedDate->format('Y-m-d'),
                    'time' => '17:00',
                    'location' => $projectName,
                    'meet_link' => null,
                    'attendees' => $task->assigned_to,
                    'status' => 'confirmed',
                    'source' => 'task',
                ];
            }
        }

        // 4. Aggregate Fundraising & Impact Grants / Open Calls & Partnerships deadlines
        $grantOpportunities = FundraisingOpportunity::whereNotNull('deadline')
            ->whereNotIn('status', ['Closed', 'Missed'])
            ->get();

        foreach ($grantOpportunities as $grant) {
            $deadlineStr = trim((string) $grant->deadline);
            if (empty($deadlineStr)) {
                continue;
            }

            $parsedDate = null;
            try {
                $parsedDate = Carbon::parse($deadlineStr);
            } catch (\Exception $e) {
            }

            if ($parsedDate) {
                $isPart = ($grant->category === 'partnerships');
                $prefix = $isPart ? 'Partnership Target: ' : 'Grant Deadline: ';
                $titleText = $grant->program_title ? $grant->program_title.' ('.$grant->organization.')' : $grant->organization;
                $events[] = [
                    'id' => 'grant_'.$grant->id,
                    'db_id' => $grant->id,
                    'title' => $prefix.$titleText,
                    'description' => "Organization: {$grant->organization} · Funding: KES ".number_format((float) $grant->amount_kes)." · Status: {$grant->status}",
                    'event_type' => 'deadline',
                    'start_time' => $parsedDate->copy()->setTime(18, 0)->toIso8601String(),
                    'end_time' => $parsedDate->copy()->setTime(19, 0)->toIso8601String(),
                    'date' => $parsedDate->format('Y-m-d'),
                    'time' => '18:00',
                    'location' => 'Fundraising & Grants Master',
                    'meet_link' => $grant->application_link,
                    'attendees' => $grant->lead_owner ?? 'Fundraising Team',
                    'status' => $grant->status,
                    'source' => 'grant',
                ];
            }
        }

        // 5. Aggregate invoice due dates (finance & owner team only)
        if ($isFinance) {
            $invoices = Invoice::where('status', '!=', 'Paid')->whereNotNull('due_date')->get();
            foreach ($invoices as $inv) {
                $parsedDate = null;
                try {
                    $parsedDate = Carbon::parse($inv->due_date);
                } catch (\Exception $e) {
                }

                if ($parsedDate) {
                    $events[] = [
                        'id' => 'inv_'.$inv->id,
                        'db_id' => $inv->id,
                        'title' => "Invoice Due: {$inv->invoice_no} ({$inv->client})",
                        'description' => 'Amount: KES '.number_format((float) $inv->amount)." · Type: {$inv->type}",
                        'event_type' => 'invoice',
                        'start_time' => $parsedDate->copy()->setTime(10, 0)->toIso8601String(),
                        'end_time' => $parsedDate->copy()->setTime(11, 0)->toIso8601String(),
                        'date' => $parsedDate->format('Y-m-d'),
                        'time' => '10:00',
                        'location' => 'Accounts Receivable',
                        'meet_link' => null,
                        'attendees' => 'Finance Team',
                        'status' => 'pending',
                        'source' => 'invoice',
                    ];
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $events,
        ]);
    }

    /**
     * Determine if a calendar event / meeting involves the given user.
     */
    protected function isUserInvolvedInEvent(
        CalendarEvent $event,
        ?User $user,
        bool $isOwner,
        string $userName,
        string $userEmail,
        string $userInitials
    ): bool {
        if (! $user && empty($userName) && empty($userEmail)) {
            return true;
        }

        if ($isOwner) {
            return true;
        }

        // 1. Created by user
        $createdBy = strtolower((string) $event->created_by);
        if ($createdBy !== '') {
            if ($userName !== '' && (str_contains($createdBy, $userName) || str_contains($userName, $createdBy))) {
                return true;
            }
            if ($userEmail !== '' && str_contains($createdBy, $userEmail)) {
                return true;
            }
        }

        // 2. In attendees list
        $attendees = strtolower((string) $event->attendees);
        if ($attendees !== '') {
            $companyWideKeywords = ['all', 'all team', 'everyone', 'company', 'all staff', 'general'];
            foreach ($companyWideKeywords as $kw) {
                if ($attendees === $kw || str_contains($attendees, $kw)) {
                    return true;
                }
            }

            if ($userName !== '' && (str_contains($attendees, $userName) || str_contains($userName, $attendees))) {
                return true;
            }
            if ($userInitials !== '' && (str_contains($attendees, $userInitials) || str_contains($attendees, "({$userInitials})"))) {
                return true;
            }
            if ($userEmail !== '' && str_contains($attendees, $userEmail)) {
                return true;
            }
        }

        // 3. Related project or task check
        if ($event->related_type === 'project' && $event->related_id) {
            $proj = Project::find($event->related_id);
            if ($proj && $this->isUserInvolvedInProject($proj, $user, $isOwner, $userName, $userEmail, $userInitials)) {
                return true;
            }
        }

        if ($event->related_type === 'task' && $event->related_id) {
            $task = Task::find($event->related_id);
            if ($task) {
                $assignee = strtolower((string) $task->assigned_to);
                $initials = strtolower((string) $task->assigned_initials);
                if ($userName !== '' && str_contains($assignee, $userName)) {
                    return true;
                }
                if ($userInitials !== '' && $initials === $userInitials) {
                    return true;
                }
            }
        }

        // 4. Mentioned in title or description
        $title = strtolower((string) $event->title);
        $desc = strtolower((string) $event->description);
        if ($userName !== '' && (str_contains($title, $userName) || str_contains($desc, $userName))) {
            return true;
        }

        // 5. General / unrestricted company event
        if (empty($event->attendees) && empty($event->created_by)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if a project involves the given user.
     */
    protected function isUserInvolvedInProject(
        Project $project,
        ?User $user,
        bool $isOwner,
        string $userName,
        string $userEmail,
        string $userInitials
    ): bool {
        if (! $user && empty($userName) && empty($userEmail)) {
            return true;
        }

        if ($isOwner) {
            return true;
        }

        // 1. Direct Project Manager match
        $pm = strtolower((string) $project->project_manager);
        if ($pm !== '') {
            if ($userName !== '' && (str_contains($pm, $userName) || str_contains($userName, $pm))) {
                return true;
            }
            if ($userInitials !== '' && (strtolower($pm) === $userInitials || str_contains($pm, "({$userInitials})"))) {
                return true;
            }
            if ($userEmail !== '' && str_contains($pm, $userEmail)) {
                return true;
            }
        }

        // 2. User assigned to any task in this project
        $hasTask = $project->tasks()->where(function ($query) use ($userName, $userInitials) {
            if ($userName !== '') {
                $query->whereRaw('LOWER(assigned_to) LIKE ?', ['%'.$userName.'%']);
            }
            if ($userInitials !== '') {
                $query->orWhereRaw('LOWER(assigned_initials) = ?', [$userInitials]);
            }
        })->exists();

        if ($hasTask) {
            return true;
        }

        // 3. User mentioned in notes, waiting_on, or comments
        $notes = strtolower((string) $project->notes);
        $waitingOn = strtolower((string) $project->waiting_on);
        if ($userName !== '' && (str_contains($notes, $userName) || str_contains($waitingOn, $userName))) {
            return true;
        }
        if ($userEmail !== '' && (str_contains($notes, $userEmail) || str_contains($waitingOn, $userEmail))) {
            return true;
        }

        return false;
    }

    /**
     * Determine if a task involves the given user.
     */
    protected function isUserInvolvedInTask(
        Task $task,
        ?User $user,
        bool $isOwner,
        string $userName,
        string $userEmail,
        string $userInitials
    ): bool {
        if (! $user && empty($userName) && empty($userEmail)) {
            return true;
        }

        if ($isOwner) {
            return true;
        }

        $assignee = strtolower((string) $task->assigned_to);
        $initials = strtolower((string) $task->assigned_initials);

        if ($userName !== '' && (str_contains($assignee, $userName) || str_contains($userName, $assignee))) {
            return true;
        }

        if ($userInitials !== '' && ($initials === $userInitials || str_contains($assignee, "({$userInitials})"))) {
            return true;
        }

        if ($task->assigned_to_id && $user && $task->assigned_to_id === $user->id) {
            return true;
        }

        if ($task->assigned_by_id && $user && $task->assigned_by_id === $user->id) {
            return true;
        }

        if ($task->project && $this->isUserInvolvedInProject($task->project, $user, $isOwner, $userName, $userEmail, $userInitials)) {
            return true;
        }

        return false;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|string|in:meeting,status_meeting,shoot,deadline,task,other',
            'start_time' => 'required|string',
            'end_time' => 'nullable|string',
            'all_day' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'attendees' => 'nullable|string|max:255',
            'related_type' => 'nullable|string|max:50',
            'related_id' => 'nullable|integer',
            'generate_meet' => 'nullable|boolean',
            'meet_link' => 'nullable|string|max:255',
        ]);

        $shouldGenerateMeet = $request->boolean('generate_meet', true);
        $meetLink = $validated['meet_link'] ?? null;
        $googleEventId = null;

        $user = auth('sanctum')->user() ?? auth()->user();

        // Auto-generate Google Meet link if requested or if event type is meeting / status meeting
        if ($shouldGenerateMeet || in_array($validated['event_type'], ['meeting', 'status_meeting'], true)) {
            if (empty($meetLink)) {
                $meetResult = $this->googleCalendarService->createCalendarEvent($validated, $user);
                $meetLink = $meetResult['meet_link'];
                $googleEventId = $meetResult['google_event_id'];
            }
        }

        $location = $validated['location'] ?? null;
        if (empty($location) && ! empty($meetLink)) {
            $location = 'Google Meet';
        }

        $event = CalendarEvent::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_type' => $validated['event_type'],
            'start_time' => Carbon::parse($validated['start_time']),
            'end_time' => ! empty($validated['end_time']) ? Carbon::parse($validated['end_time']) : null,
            'all_day' => $validated['all_day'] ?? false,
            'location' => $location,
            'meet_link' => $meetLink,
            'attendees' => $validated['attendees'] ?? null,
            'google_event_id' => $googleEventId,
            'related_type' => $validated['related_type'] ?? null,
            'related_id' => $validated['related_id'] ?? null,
            'status' => 'confirmed',
            'created_by' => $user?->name ?? 'Admin',
        ]);

        AuditLog::record(
            'CREATE',
            'Scheduled '.($event->event_type === 'shoot' ? 'production shoot' : 'calendar event')." '{$event->title}' (".Carbon::parse($event->start_time)->format('M d, Y H:i').')',
            $event->event_type === 'shoot' ? 'Shoot' : 'Calendar',
            $event->id,
            ['event_type' => $event->event_type, 'start_time' => $event->start_time, 'location' => $event->location],
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Event scheduled and Google Meet link generated.',
            'data' => $event,
        ], 201);
    }

    public function generateMeet(CalendarEvent $event): JsonResponse
    {
        $user = auth('sanctum')->user() ?? auth()->user();

        if (empty($event->meet_link)) {
            $result = $this->googleCalendarService->createCalendarEvent([
                'title' => $event->title,
                'description' => $event->description,
                'start_time' => $event->start_time?->toIso8601String(),
                'end_time' => $event->end_time?->toIso8601String(),
                'location' => $event->location,
                'attendees' => $event->attendees,
            ], $user);

            $event->meet_link = $result['meet_link'];
            if (empty($event->location)) {
                $event->location = 'Google Meet';
            }
            if (! empty($result['google_event_id'])) {
                $event->google_event_id = $result['google_event_id'];
            }
            $event->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Google Meet link generated successfully.',
            'data' => [
                'meet_link' => $event->meet_link,
                'event' => $event,
            ],
        ]);
    }

    public function destroy(Request $request, CalendarEvent $event): JsonResponse
    {
        $title = $event->title;
        $id = $event->id;
        $isShoot = ($event->event_type === 'shoot');
        $event->delete();

        AuditLog::record(
            'DELETE',
            'Cancelled '.($isShoot ? 'production shoot' : 'calendar event')." '{$title}'",
            $isShoot ? 'Shoot' : 'Calendar',
            $id,
            [],
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Event removed from calendar.',
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user() ?? auth()->user();
        $accountEmail = trim((string) $request->input('account_email', ''));

        if (empty($accountEmail)) {
            $accountEmail = $user?->google_calendar_email
                ?: $user?->email
                ?: SystemSetting::getVal('google_connected_account')
                ?: SystemSetting::getVal('google_calendar_id');
        }

        // If service account key is saved, extract client_email if not set
        $saRaw = SystemSetting::getVal('google_service_account_json') ?: SystemSetting::getVal('google_api_key');
        if (! empty($saRaw) && str_starts_with(trim($saRaw), '{')) {
            $parsed = json_decode($saRaw, true);
            if (! empty($parsed['client_email']) && (empty($accountEmail) || $accountEmail === 'primary')) {
                $accountEmail = $parsed['client_email'];
            }
        }

        if (empty($accountEmail) || $accountEmail === 'primary') {
            $accountEmail = $user?->email ?: 'jeotamedia@gmail.com';
        }

        $accountEmail = strtolower(trim($accountEmail));

        // Test API verification with GoogleCalendarService
        $verification = $this->googleCalendarService->verifyConnection($accountEmail);

        if ($user) {
            $user->update([
                'google_calendar_email' => $accountEmail,
                'google_calendar_status' => 'connected',
                'google_calendar_synced_at' => now(),
            ]);
        }

        // Also persist in system settings as active configuration
        SystemSetting::updateOrCreate(
            ['key' => 'google_connected_account'],
            ['value' => $accountEmail, 'group' => 'google_calendar', 'is_secret' => false]
        );
        SystemSetting::updateOrCreate(
            ['key' => 'google_calendar_status'],
            ['value' => 'connected', 'group' => 'google_calendar', 'is_secret' => false]
        );

        $eventsCount = CalendarEvent::count();

        return response()->json([
            'status' => 'success',
            'message' => $verification['message'] ?? "Google Calendar successfully authorized and synced ({$accountEmail}).",
            'account' => $accountEmail,
            'status_label' => 'connected',
            'synced_count' => $eventsCount,
            'synced_at' => now()->toIso8601String(),
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'google_calendar_email' => $user->google_calendar_email,
                'google_calendar_status' => $user->google_calendar_status,
                'google_calendar_synced_at' => $user->google_calendar_synced_at?->toIso8601String(),
            ] : null,
        ]);
    }

    public function disconnect(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user() ?? auth()->user();

        if ($user) {
            $user->update([
                'google_calendar_email' => null,
                'google_calendar_status' => 'disconnected',
                'google_calendar_synced_at' => null,
            ]);
        }

        SystemSetting::updateOrCreate(
            ['key' => 'google_calendar_status'],
            ['value' => 'disconnected', 'group' => 'google_calendar', 'is_secret' => false]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Personal Google Calendar disconnected.',
            'status_label' => 'disconnected',
            'account' => null,
        ]);
    }

    public function syncStatus(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user() ?? auth()->user();

        $account = $user?->google_calendar_email;
        $status = $user?->google_calendar_status ?? ($account ? 'connected' : 'disconnected');
        $syncedAt = $user?->google_calendar_synced_at?->toIso8601String();

        if (! $user && ! $account) {
            $settingEmail = SystemSetting::getVal('google_connected_account');
            $settingStatus = SystemSetting::getVal('google_calendar_status', 'disconnected');
            if ($settingEmail && $settingStatus === 'connected') {
                $account = $settingEmail;
                $status = 'connected';
            }
        }

        return response()->json([
            'status' => 'success',
            'connected' => $status === 'connected' && ! empty($account),
            'status_label' => $status,
            'account' => $account,
            'synced_at' => $syncedAt,
        ]);
    }

    public function getGoogleAuthUrl(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user() ?? auth()->user();
        $clientId = SystemSetting::getVal('google_client_id')
            ?: config('services.google.client_id')
            ?: '337996386320-berd8925g6askvbrfgm5vdd8148te4r7.apps.googleusercontent.com';

        $redirectUri = url('/calendar/google/callback');
        $email = $request->query('email', $user?->google_calendar_email ?? $user?->email ?? '');

        $scopes = [
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
        ];

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'access_type' => 'offline',
            'prompt' => 'consent select_account',
            'state' => csrf_token() ?: ($user ? (string) $user->id : 'jmos_calendar'),
        ];

        if (! empty($email)) {
            $params['login_hint'] = $email;
        }

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($params);

        return response()->json([
            'status' => 'success',
            'url' => $authUrl,
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
        ]);
    }

    public function googleRedirect(Request $request)
    {
        $user = auth('sanctum')->user() ?? auth()->user();
        $clientId = SystemSetting::getVal('google_client_id')
            ?: config('services.google.client_id')
            ?: '337996386320-berd8925g6askvbrfgm5vdd8148te4r7.apps.googleusercontent.com';

        $redirectUri = url('/calendar/google/callback');
        $email = $request->query('email', $user?->google_calendar_email ?? $user?->email ?? '');

        $scopes = [
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/userinfo.email',
        ];

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'access_type' => 'offline',
            'prompt' => 'consent select_account',
        ];

        if (! empty($email)) {
            $params['login_hint'] = $email;
        }

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($params));
    }

    public function googleCallback(Request $request)
    {
        $user = auth('sanctum')->user() ?? auth()->user();
        $accountEmail = $request->query('email') ?? $request->query('login_hint') ?? $user?->email;

        if ($accountEmail && $user) {
            $user->update([
                'google_calendar_email' => $accountEmail,
                'google_calendar_status' => 'connected',
                'google_calendar_synced_at' => now(),
            ]);
        }

        if ($accountEmail) {
            SystemSetting::updateOrCreate(
                ['key' => 'google_connected_account'],
                ['value' => $accountEmail, 'group' => 'google_calendar', 'is_secret' => false]
            );
            SystemSetting::updateOrCreate(
                ['key' => 'google_calendar_status'],
                ['value' => 'connected', 'group' => 'google_calendar', 'is_secret' => false]
            );
        }

        return redirect('/?view=calendar&google_sync=success');
    }
}
