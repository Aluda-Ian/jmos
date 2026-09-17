<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\LeadCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadCallController extends Controller
{
    /**
     * List all calls logged across the CRM
     */
    public function index(Request $request): JsonResponse
    {
        $query = LeadCall::with(['lead', 'client', 'deal'])->latest('call_time')->latest('id');

        if ($request->filled('lead_id')) {
            $query->where('lead_id', $request->integer('lead_id'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('deal_id')) {
            $query->where('deal_id', $request->integer('deal_id'));
        }

        if ($request->filled('outcome')) {
            $query->where('outcome', $request->string('outcome'));
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get(),
        ]);
    }

    /**
     * Log a new call
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'client_id' => 'nullable|exists:clients,id',
            'deal_id' => 'nullable|exists:deals,id',
            'call_type' => 'required|in:Outbound,Inbound',
            'call_status' => 'nullable|in:Completed,Scheduled,Missed,Cancelled',
            'purpose' => 'required|string|max:100',
            'outcome' => 'nullable|string|max:100',
            'duration_minutes' => 'nullable|integer|min:0',
            'call_time' => 'nullable|date',
            'logged_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['call_time'])) {
            $validated['call_time'] = now();
        }

        if (empty($validated['call_status'])) {
            $validated['call_status'] = 'Completed';
        }

        $call = LeadCall::create($validated);

        // If logged for a lead, bump lead status to 'Contacted' or 'In Discussion' if still 'New'
        if (! empty($validated['lead_id'])) {
            $lead = Lead::find($validated['lead_id']);
            if ($lead && $lead->lead_status === 'New') {
                $lead->update(['lead_status' => 'Contacted']);
            }
        }

        AuditLog::record(
            'CALL',
            "Logged {$call->call_type} call ({$call->purpose} - {$call->outcome}) with ".($call->lead?->lead_name ?? $call->client?->client_name ?? 'Client'),
            'LeadCall',
            $call->id,
            [],
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Call logged successfully.',
            'data' => $call->load(['lead', 'client', 'deal']),
        ], 201);
    }

    /**
     * Delete a call log
     */
    public function destroy(LeadCall $leadCall): JsonResponse
    {
        $leadCall->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Call log deleted.',
        ]);
    }
}
