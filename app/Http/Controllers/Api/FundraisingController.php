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

    public function importCsv(Request $request): JsonResponse
    {
        $defaultCategory = $request->input('default_category', 'partnerships');
        $csvContent = '';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if (! $file->isValid()) {
                return response()->json(['status' => 'error', 'message' => 'Invalid file uploaded.'], 422);
            }
            $csvContent = file_get_contents($file->getRealPath());
        } elseif ($request->filled('csv_data')) {
            $csvContent = $request->string('csv_data')->toString();
        } else {
            return response()->json(['status' => 'error', 'message' => 'Please provide a CSV file or csv_data payload.'], 422);
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($csvContent));
        if (count($lines) < 2) {
            return response()->json(['status' => 'error', 'message' => 'CSV file appears to be empty or missing header.'], 422);
        }

        // Parse header line
        $rawHeaders = str_getcsv(array_shift($lines));
        $normalizedHeaders = [];
        foreach ($rawHeaders as $idx => $h) {
            $clean = strtolower(trim((string) $h));
            $clean = str_replace([' ', '_', '-', '/', '\\', '.'], '', $clean);
            $normalizedHeaders[$idx] = $clean;
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $currentSection = 'Grantmakers / CBOs';

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $row = str_getcsv($line);
            if (empty(array_filter($row, fn ($v) => trim((string) $v) !== ''))) {
                continue;
            }

            // Detect section headers if present (e.g. "a. Grantmakers / CBOs")
            $firstCell = trim((string) ($row[0] ?? ''));
            $secondCell = trim((string) ($row[1] ?? ''));
            $fullRowText = implode(' ', $row);

            if (preg_match('/^[a-d]\.\s*(.*)/i', $firstCell, $m) || preg_match('/^[a-d]\.\s*(.*)/i', $secondCell, $m)) {
                $sectionName = $m[1] ?? $firstCell;
                if (stripos($sectionName, 'grantmaker') !== false || stripos($sectionName, 'cbo') !== false) {
                    $currentSection = 'Grantmakers / CBOs';
                } elseif (stripos($sectionName, 'association') !== false || stripos($sectionName, 'cooperative') !== false) {
                    $currentSection = 'Associations / Cooperatives';
                } elseif (stripos($sectionName, 'corporate') !== false) {
                    $currentSection = 'Corporate Institutions';
                } elseif (stripos($sectionName, 'academia') !== false || stripos($sectionName, 'education') !== false) {
                    $currentSection = 'Academia / Educational Institutions';
                }

                continue;
            }

            // Extract values by mapped header
            $data = [];
            foreach ($row as $colIdx => $val) {
                $h = $normalizedHeaders[$colIdx] ?? '';
                $trimmed = trim((string) $val);

                if (in_array($h, ['organization', 'entityname', 'entity', 'name', 'org', 'company'])) {
                    $data['organization'] = $trimmed;
                } elseif (in_array($h, ['programtitle', 'program', 'particulars', 'title', 'project'])) {
                    $data['program_title'] = $trimmed;
                } elseif (in_array($h, ['nature', 'fundingtype', 'type'])) {
                    $data['funding_type'] = $trimmed;
                } elseif (in_array($h, ['section', 'classification', 'partnershipentitytype', 'entitytype', 'group'])) {
                    $data['partnership_entity_type'] = $trimmed;
                } elseif (in_array($h, ['website', 'portal', 'applicationlink', 'link', 'url', 'web'])) {
                    $data['application_link'] = $trimmed;
                } elseif (in_array($h, ['status', 'state'])) {
                    $data['status'] = $trimmed;
                } elseif (in_array($h, ['comments', 'notes', 'description', 'remarks'])) {
                    $data['notes'] = $trimmed;
                } elseif (in_array($h, ['amount', 'amountkes', 'fundingvalue', 'value', 'budget'])) {
                    $num = preg_replace('/[^0-9.]/', '', $trimmed);
                    if ($num !== '') {
                        $data['amount_kes'] = (float) $num;
                    }
                } elseif (in_array($h, ['deadline', 'timeline', 'date'])) {
                    $data['deadline'] = $trimmed;
                } elseif (in_array($h, ['category', 'module'])) {
                    $data['category'] = $trimmed;
                }
            }

            // Fallback column index mapping if headers were generic
            if (empty($data['organization']) && ! empty($row[1]) && ! is_numeric($row[1])) {
                $data['organization'] = trim((string) $row[1]);
            } elseif (empty($data['organization']) && ! empty($row[0]) && ! is_numeric($row[0])) {
                $data['organization'] = trim((string) $row[0]);
            }

            if (empty($data['organization'])) {
                $skipped++;

                continue;
            }

            // Clean & set defaults
            $category = $data['category'] ?? $defaultCategory;
            if (! in_array($category, ['open_calls', 'partnerships', 'fellowships'])) {
                $category = $defaultCategory;
            }

            $entityType = $data['partnership_entity_type'] ?? $currentSection;
            $fundingType = $data['funding_type'] ?? ($category === 'partnerships' ? 'Partnership Invitation' : 'Grants Application');
            $status = $data['status'] ?? ($category === 'partnerships' ? 'Pending' : 'Identified');
            $amountKes = $data['amount_kes'] ?? 0;

            $record = FundraisingOpportunity::updateOrCreate(
                ['organization' => $data['organization']],
                [
                    'program_title' => $data['program_title'] ?? ($category === 'partnerships' ? 'Strategic Partnership' : 'Open Call'),
                    'application_link' => $data['application_link'] ?? null,
                    'amount_kes' => $amountKes,
                    'amount_display' => $amountKes > 0 ? 'KES '.number_format($amountKes) : ($category === 'partnerships' ? 'Partnership' : 'Grant'),
                    'funding_type' => $fundingType,
                    'deadline' => $data['deadline'] ?? 'Rolling basis',
                    'status' => $status,
                    'category' => $category,
                    'partnership_entity_type' => $entityType,
                    'notes' => $data['notes'] ?? null,
                ]
            );

            if ($record->wasRecentlyCreated) {
                $imported++;
            } else {
                $updated++;
            }
        }

        AuditLog::record('IMPORT', "Imported {$imported} new and updated {$updated} fundraising master records from CSV", 'FundraisingOpportunity', 0, ['imported' => $imported, 'updated' => $updated, 'skipped' => $skipped], $request);

        return response()->json([
            'status' => 'success',
            'message' => "CSV imported successfully: {$imported} added, {$updated} updated, {$skipped} skipped.",
            'imported_count' => $imported,
            'updated_count' => $updated,
            'skipped_count' => $skipped,
        ]);
    }
}
