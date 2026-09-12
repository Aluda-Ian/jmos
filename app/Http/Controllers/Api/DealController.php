<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Deal::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'stage' => 'required|in:lead,meeting,proposal,negotiation,won',
            'value' => 'required|numeric',
            'meta_text' => 'nullable|string',
        ]);

        $deal = Deal::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Deal added to pipeline.',
            'data' => $deal,
        ], 201);
    }

    public function update(Request $request, Deal $deal): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'stage' => 'nullable|in:lead,meeting,proposal,negotiation,won',
            'value' => 'nullable|numeric',
            'meta_text' => 'nullable|string',
        ]);

        $deal->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Deal updated.',
            'data' => $deal,
        ]);
    }

    /**
     * Win & Spin Up Project Cascade Flow
     */
    public function win(Deal $deal): JsonResponse
    {
        $deal->update([
            'stage' => 'won',
            'is_won' => true,
        ]);

        // 1. Create live project
        $project = Project::create([
            'project_name' => $deal->title,
            'client' => $deal->client_name,
            'project_type' => 'Brand Film',
            'project_manager' => 'Barny Kiome',
            'stage' => 'brief',
            'status' => 'On track',
            'priority' => 'High',
            'deadline' => 'Sep 30',
            'budget' => $deal->value,
            'progress_pct' => 10,
            'waiting_on' => 'us',
        ]);

        // 2. Generate 8 tasks from the recipe
        $recipeTasks = [
            ['title' => 'Client Briefing & Kickoff', 'stage' => 'in_progress', 'assigned_to' => 'Barny Kiome', 'assigned_initials' => 'BK', 'assigned_color' => '#C52523'],
            ['title' => 'Concept & Storyboard', 'stage' => 'todo', 'assigned_to' => 'Lesley Chacha', 'assigned_initials' => 'LC', 'assigned_color' => '#8A5A2B'],
            ['title' => 'Pre-Production & Location Scout', 'stage' => 'todo', 'assigned_to' => 'Amos Muthama', 'assigned_initials' => 'AM', 'assigned_color' => '#2B6E8A'],
            ['title' => 'Principal Photography / Shoot', 'stage' => 'todo', 'assigned_to' => 'Amos Muthama', 'assigned_initials' => 'AM', 'assigned_color' => '#2B6E8A'],
            ['title' => 'Assembly Edit v1', 'stage' => 'todo', 'assigned_to' => 'Stephen Otieno', 'assigned_initials' => 'SO', 'assigned_color' => '#5A7A2B'],
            ['title' => 'Color Grading & Sound Pass', 'stage' => 'todo', 'assigned_to' => 'Stephen Otieno', 'assigned_initials' => 'SO', 'assigned_color' => '#5A7A2B'],
            ['title' => 'Internal & Client Review', 'stage' => 'todo', 'assigned_to' => 'Barny Kiome', 'assigned_initials' => 'BK', 'assigned_color' => '#C52523'],
            ['title' => 'Final Export & Deliverables Master', 'stage' => 'todo', 'assigned_to' => 'Stephen Otieno', 'assigned_initials' => 'SO', 'assigned_color' => '#5A7A2B'],
        ];

        foreach ($recipeTasks as $t) {
            $t['project_id'] = $project->id;
            Task::create($t);
        }

        // 3. Draft deposit invoice (60%)
        $maxNum = 145;
        $allInvoices = Invoice::pluck('invoice_no');
        foreach ($allInvoices as $no) {
            if (preg_match('/(\d+)/', $no, $m)) {
                $n = (int) $m[1];
                if ($n > $maxNum) {
                    $maxNum = $n;
                }
            }
        }
        $nextNo = 'JM-0'.($maxNum + 1);

        $depositInvoice = Invoice::create([
            'invoice_no' => $nextNo,
            'client' => $deal->client_name,
            'type' => 'Deposit 60%',
            'amount' => $deal->value * 0.60,
            'method' => null,
            'etims' => false,
            'status' => 'Sent',
            'due_date' => now()->addDays(7)->format('M d'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Deal won! Project created, 8 tasks generated, and invoice drafted.',
            'project' => $project,
            'invoice' => $depositInvoice,
        ]);
    }

    public function destroy(Deal $deal): JsonResponse
    {
        $deal->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Deal removed from pipeline.',
        ]);
    }
}
