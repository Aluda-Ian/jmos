<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * List all leads with filtering, search & summary KPIs
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lead::query();

        if ($request->filled('search')) {
            $s = $request->string('search')->trim();
            $query->where(function ($q) use ($s) {
                $q->where('lead_name', 'like', "%{$s}%")
                    ->orWhere('company', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->string('status');
            if ($status === 'active') {
                $query->where('is_converted', false)->where('lead_status', '!=', 'Junk/Lost');
            } elseif ($status === 'converted') {
                $query->where('is_converted', true);
            } else {
                $query->where('lead_status', $status);
            }
        }

        if ($request->filled('source')) {
            $query->where('lead_source', $request->string('source'));
        }

        if ($request->filled('owner')) {
            $query->where('lead_owner', $request->string('owner'));
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->string('rating'));
        }

        $leads = $query->withCount(['calls', 'meetings'])->latest()->get();

        // Calculate CRM KPIs
        $allLeads = Lead::all();
        $stats = [
            'total_leads' => $allLeads->count(),
            'active_leads' => $allLeads->where('is_converted', false)->where('lead_status', '!=', 'Junk/Lost')->count(),
            'qualified_leads' => $allLeads->where('lead_status', 'Qualified')->count(),
            'converted_leads' => $allLeads->where('is_converted', true)->count(),
            'hot_leads' => $allLeads->where('rating', 'Hot')->count(),
            'pipeline_potential' => (float) $allLeads->where('is_converted', false)->sum('annual_revenue'),
            'total_calls_logged' => LeadCall::count(),
            'conversion_rate' => $allLeads->count() > 0
                ? round(($allLeads->where('is_converted', true)->count() / $allLeads->count()) * 100, 1)
                : 0,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $leads,
            'stats' => $stats,
        ]);
    }

    /**
     * Store new lead
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'lead_name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'lead_source' => 'nullable|string|max:100',
            'lead_status' => 'nullable|string|max:100',
            'lead_owner' => 'nullable|string|max:255',
            'rating' => 'nullable|string|in:Hot,Warm,Cold',
            'industry' => 'nullable|string|max:100',
            'annual_revenue' => 'nullable|numeric',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['lead_name'])) {
            $nameParts = array_filter([$validated['first_name'] ?? '', $validated['last_name'] ?? '']);
            $validated['lead_name'] = ! empty($nameParts) ? implode(' ', $nameParts) : ($validated['company'] ?? 'New Prospect');
        }

        $lead = Lead::create($validated);

        AuditLog::record('CREATE', "Captured new lead '{$lead->lead_name}'".($lead->company ? " from {$lead->company}" : ''), 'Lead', $lead->id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Lead created successfully.',
            'data' => $lead,
        ], 201);
    }

    /**
     * Show single lead with complete Zoho CRM journey history
     */
    public function show(Lead $lead): JsonResponse
    {
        $lead->load([
            'calls',
            'meetings',
            'convertedClient',
            'convertedContact',
            'convertedDeal',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $lead,
        ]);
    }

    /**
     * Update lead record
     */
    public function update(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'lead_name' => 'sometimes|required|string|max:255',
            'company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'lead_source' => 'nullable|string|max:100',
            'lead_status' => 'nullable|string|max:100',
            'lead_owner' => 'nullable|string|max:255',
            'rating' => 'nullable|string|in:Hot,Warm,Cold',
            'industry' => 'nullable|string|max:100',
            'annual_revenue' => 'nullable|numeric',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $lead->update($validated);

        AuditLog::record('UPDATE', "Updated lead record '{$lead->lead_name}'", 'Lead', $lead->id, $validated, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Lead updated successfully.',
            'data' => $lead->fresh(['calls', 'meetings']),
        ]);
    }

    /**
     * Delete lead
     */
    public function destroy(Request $request, Lead $lead): JsonResponse
    {
        $name = $lead->lead_name;
        $id = $lead->id;

        $lead->delete();

        AuditLog::record('DELETE', "Removed lead '{$name}'", 'Lead', $id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Lead removed.',
        ]);
    }

    /**
     * Zoho CRM Lead Conversion Engine:
     * Converts a qualified lead into a Contact, an Account (Client), and a Pipeline Deal.
     */
    public function convert(Request $request, Lead $lead): JsonResponse
    {
        if ($lead->is_converted) {
            return response()->json([
                'status' => 'error',
                'message' => 'This lead has already been converted.',
            ], 422);
        }

        $validated = $request->validate([
            'create_account' => 'nullable|boolean',
            'account_name' => 'nullable|string|max:255',
            'account_id' => 'nullable|exists:clients,id',

            'create_contact' => 'nullable|boolean',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_title' => 'nullable|string|max:255',

            'create_deal' => 'nullable|boolean',
            'deal_title' => 'nullable|string|max:255',
            'deal_value' => 'nullable|numeric',
            'deal_stage' => 'nullable|in:lead,meeting,proposal,negotiation,won',
            'expected_close_date' => 'nullable|date',
        ]);

        $createAccount = $request->boolean('create_account', true);
        $createContact = $request->boolean('create_contact', true);
        $createDeal = $request->boolean('create_deal', true);

        $client = null;
        $contact = null;
        $deal = null;

        // 1. Account / Client Creation or Association
        if ($createAccount) {
            if (! empty($validated['account_id'])) {
                $client = Client::find($validated['account_id']);
            } else {
                $accountName = ! empty($validated['account_name'])
                    ? $validated['account_name']
                    : ($lead->company ?: $lead->lead_name);

                $client = Client::create([
                    'client_name' => $accountName,
                    'client_type' => $lead->industry ?: 'Corporate',
                    'contact_person' => $lead->lead_name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'address' => array_filter([$lead->address, $lead->city]) ? implode(', ', array_filter([$lead->address, $lead->city])) : null,
                    'website' => $lead->website,
                    'owner' => $lead->lead_owner ?: 'Barny Kiome',
                    'project_value' => $validated['deal_value'] ?? $lead->annual_revenue ?? 0,
                    'project_status' => 'Active',
                    'notes' => 'Converted from Lead on '.now()->format('M d, Y').'. Initial Notes: '.($lead->notes ?: 'None'),
                ]);
            }
        }

        // 2. Contact Creation
        if ($createContact) {
            $contactName = ! empty($validated['contact_name']) ? $validated['contact_name'] : $lead->lead_name;
            $contact = Contact::create([
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'contact_name' => $contactName,
                'company_name' => $client ? $client->client_name : $lead->company,
                'client_id' => $client ? $client->id : null,
                'lead_id' => $lead->id,
                'title' => $validated['contact_title'] ?? $lead->title,
                'email' => $validated['contact_email'] ?? $lead->email,
                'phone' => $validated['contact_phone'] ?? $lead->phone,
                'owner' => $lead->lead_owner ?: 'Jeota Media',
                'notes' => $lead->notes,
            ]);
        }

        // 3. Pipeline Deal Creation
        if ($createDeal) {
            $dealTitle = ! empty($validated['deal_title'])
                ? $validated['deal_title']
                : ($lead->company ? "{$lead->company} · Commercial Video" : "{$lead->lead_name} · Production Deal");

            $dealValue = ! empty($validated['deal_value'])
                ? (float) $validated['deal_value']
                : (float) ($lead->annual_revenue > 0 ? $lead->annual_revenue : 250000);

            $dealStage = ! empty($validated['deal_stage']) ? $validated['deal_stage'] : 'meeting';

            $deal = Deal::create([
                'lead_id' => $lead->id,
                'client_id' => $client ? $client->id : null,
                'title' => $dealTitle,
                'client_name' => $client ? $client->client_name : ($lead->company ?: $lead->lead_name),
                'contact_person' => $contact ? $contact->contact_name : $lead->lead_name,
                'deal_owner' => $lead->lead_owner ?: 'Barny Kiome',
                'stage' => $dealStage,
                'value' => $dealValue,
                'expected_close_date' => $validated['expected_close_date'] ?? now()->addDays(30)->toDateString(),
                'meta_text' => 'Converted from Lead on '.now()->format('M d, Y'),
                'is_won' => ($dealStage === 'won'),
            ]);
        }

        // 4. Carry over Call Logs & Calendar Meetings
        LeadCall::where('lead_id', $lead->id)->update([
            'client_id' => $client ? $client->id : null,
            'deal_id' => $deal ? $deal->id : null,
        ]);

        CalendarEvent::where('related_type', 'Lead')
            ->where('related_id', $lead->id)
            ->update([
                'related_type' => $deal ? 'Deal' : 'Client',
                'related_id' => $deal ? $deal->id : ($client ? $client->id : $lead->id),
            ]);

        // 5. Update Lead Status as Converted
        $lead->update([
            'is_converted' => true,
            'converted_at' => now(),
            'converted_client_id' => $client ? $client->id : null,
            'converted_contact_id' => $contact ? $contact->id : null,
            'converted_deal_id' => $deal ? $deal->id : null,
            'lead_status' => 'Converted',
        ]);

        AuditLog::record(
            'CONVERT',
            "Converted lead '{$lead->lead_name}' into Account '{$client?->client_name}', Contact '{$contact?->contact_name}', and Deal '{$deal?->title}'",
            'Lead',
            $lead->id,
            [],
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Lead converted successfully into Account, Contact, and Pipeline Deal.',
            'lead' => $lead->fresh(['convertedClient', 'convertedContact', 'convertedDeal']),
            'account' => $client,
            'contact' => $contact,
            'deal' => $deal,
        ]);
    }
}
