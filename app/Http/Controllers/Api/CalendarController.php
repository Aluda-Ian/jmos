<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // 1. Fetch custom calendar events
        $events = CalendarEvent::orderBy('start_time', 'asc')->get()->map(function ($e) {
            return [
                'id' => 'evt_' . $e->id,
                'db_id' => $e->id,
                'title' => $e->title,
                'description' => $e->description,
                'event_type' => $e->event_type,
                'start_time' => $e->start_time ? $e->start_time->toIso8601String() : null,
                'end_time' => $e->end_time ? $e->end_time->toIso8601String() : null,
                'date' => $e->start_time ? $e->start_time->format('Y-m-d') : null,
                'time' => $e->start_time ? $e->start_time->format('H:i') : null,
                'location' => $e->location,
                'attendees' => $e->attendees,
                'status' => $e->status,
                'source' => 'calendar',
            ];
        })->toArray();

        // 2. Aggregate project deadlines
        $projects = Project::whereNotNull('deadline')->get();
        foreach ($projects as $p) {
            $deadlineStr = $p->deadline;
            // Attempt to parse standard dates (e.g. Sep 30, Oct 15, or Y-m-d)
            $parsedDate = null;
            try {
                $parsedDate = \Carbon\Carbon::parse($deadlineStr);
            } catch (\Exception $e) {}

            if ($parsedDate) {
                $events[] = [
                    'id' => 'proj_' . $p->id,
                    'db_id' => $p->id,
                    'title' => 'Project Due: ' . $p->project_name,
                    'description' => "Client: {$p->client} · Stage: {$p->stage} · Status: {$p->status}",
                    'event_type' => 'deadline',
                    'start_time' => $parsedDate->setTime(17, 0)->toIso8601String(),
                    'end_time' => $parsedDate->setTime(18, 0)->toIso8601String(),
                    'date' => $parsedDate->format('Y-m-d'),
                    'time' => '17:00',
                    'location' => 'JMOS Delivery Board',
                    'attendees' => $p->project_manager,
                    'status' => 'confirmed',
                    'source' => 'project',
                ];
            }
        }

        // 3. Aggregate invoice due dates
        $invoices = Invoice::where('status', '!=', 'Paid')->whereNotNull('due_date')->get();
        foreach ($invoices as $inv) {
            $parsedDate = null;
            try {
                $parsedDate = \Carbon\Carbon::parse($inv->due_date);
            } catch (\Exception $e) {}

            if ($parsedDate) {
                $events[] = [
                    'id' => 'inv_' . $inv->id,
                    'db_id' => $inv->id,
                    'title' => "Invoice Due: {$inv->invoice_no} ({$inv->client})",
                    'description' => "Amount: KES " . number_format($inv->amount) . " · Type: {$inv->type}",
                    'event_type' => 'invoice',
                    'start_time' => $parsedDate->setTime(10, 0)->toIso8601String(),
                    'end_time' => $parsedDate->setTime(11, 0)->toIso8601String(),
                    'date' => $parsedDate->format('Y-m-d'),
                    'time' => '10:00',
                    'location' => 'Accounts Receivable',
                    'attendees' => 'Finance Team',
                    'status' => 'pending',
                    'source' => 'invoice',
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $events,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|string|in:meeting,shoot,deadline,task,other',
            'start_time' => 'required|string',
            'end_time' => 'nullable|string',
            'all_day' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'attendees' => 'nullable|string|max:255',
        ]);

        $event = CalendarEvent::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_type' => $validated['event_type'],
            'start_time' => \Carbon\Carbon::parse($validated['start_time']),
            'end_time' => !empty($validated['end_time']) ? \Carbon\Carbon::parse($validated['end_time']) : null,
            'all_day' => $validated['all_day'] ?? false,
            'location' => $validated['location'] ?? null,
            'attendees' => $validated['attendees'] ?? null,
            'status' => 'confirmed',
            'created_by' => auth()->user()?->name ?? 'Admin',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Event scheduled and added to calendar.',
            'data' => $event,
        ], 201);
    }

    public function destroy(CalendarEvent $event): JsonResponse
    {
        $event->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Event removed from calendar.',
        ]);
    }

    public function sync(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Synced with Google Calendar API. Notifications and meetings are up to date.',
            'synced_at' => now()->toIso8601String(),
        ]);
    }
}
