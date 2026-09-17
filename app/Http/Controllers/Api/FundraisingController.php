<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FundraisingOpportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FundraisingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = FundraisingOpportunity::latest();

        if ($request->filled('category') && $request->string('category') !== 'all') {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('status') && $request->string('status') !== 'all') {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $s = $request->string('search')->trim();
            $query->where(function ($q) use ($s) {
                $q->where('organization', 'like', "%{$s}%")
                    ->orWhere('program_title', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%")
                    ->orWhere('partner_organization', 'like', "%{$s}%");
            });
        }

        $all = FundraisingOpportunity::all();
        $stats = [
            'total_opportunities' => $all->count(),
            'total_pipeline_kes' => (float) $all->sum('amount_kes'),
            'open_calls_count' => $all->where('category', 'open_calls')->count(),
            'partnerships_count' => $all->where('category', 'partnerships')->count(),
            'submitted_count' => $all->whereIn('status', ['Submitted', 'Applied', '1'])->count(),
            'active_pipeline_count' => $all->whereNotIn('status', ['Missed', 'Rejected'])->count(),
            'won_count' => $all->where('status', 'Won / Awarded')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $query->get(),
            'stats' => $stats,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization' => 'required|string|max:255',
            'program_title' => 'nullable|string|max:255',
            'application_link' => 'nullable|url|max:1000',
            'amount_kes' => 'nullable|numeric|min:0',
            'amount_display' => 'nullable|string|max:100',
            'funding_type' => 'nullable|string|max:100',
            'deadline' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:100',
            'category' => 'nullable|string|in:open_calls,partnerships,fellowships',
            'partnership_entity_type' => 'nullable|string|max:100',
            'partner_organization' => 'nullable|string|max:255',
            'lead_owner' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['amount_display']) && ! empty($validated['amount_kes'])) {
            $validated['amount_display'] = 'KES '.number_format((float) $validated['amount_kes']);
        }

        $opp = FundraisingOpportunity::create($validated);

        AuditLog::record('CREATE', "Logged fundraising grant/call '{$opp->organization}' ({$opp->program_title})", 'FundraisingOpportunity', $opp->id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => "Impact grant opportunity '{$opp->organization}' registered.",
            'data' => $opp,
        ], 201);
    }

    public function show(FundraisingOpportunity $fundraising): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $fundraising,
        ]);
    }

    public function update(Request $request, FundraisingOpportunity $fundraising): JsonResponse
    {
        $validated = $request->validate([
            'organization' => 'sometimes|required|string|max:255',
            'program_title' => 'nullable|string|max:255',
            'application_link' => 'nullable|url|max:1000',
            'amount_kes' => 'nullable|numeric|min:0',
            'amount_display' => 'nullable|string|max:100',
            'funding_type' => 'nullable|string|max:100',
            'deadline' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:100',
            'category' => 'nullable|string|in:open_calls,partnerships,fellowships',
            'partnership_entity_type' => 'nullable|string|max:100',
            'partner_organization' => 'nullable|string|max:255',
            'lead_owner' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $fundraising->update($validated);

        AuditLog::record('UPDATE', "Updated fundraising record '{$fundraising->organization}'", 'FundraisingOpportunity', $fundraising->id, $validated, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Opportunity updated successfully.',
            'data' => $fundraising,
        ]);
    }

    public function destroy(FundraisingOpportunity $fundraising): JsonResponse
    {
        $org = $fundraising->organization;
        $id = $fundraising->id;
        $fundraising->delete();

        AuditLog::record('DELETE', "Removed fundraising call '{$org}'", 'FundraisingOpportunity', $id);

        return response()->json([
            'status' => 'success',
            'message' => "Opportunity '{$org}' removed.",
        ]);
    }
}
