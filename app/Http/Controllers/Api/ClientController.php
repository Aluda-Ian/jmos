<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Client::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_type' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'owner' => 'nullable|string|max:255',
            'projects' => 'nullable|integer',
            'service' => 'nullable|string|max:255',
            'project_status' => 'nullable|string|max:100',
            'project_value' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $client = Client::create($validated);

        // Auto-generate / sync Contact for this Client
        $contactPerson = $client->contact_person ?: $client->client_name;
        Contact::create([
            'contact_name' => $contactPerson,
            'company_name' => $client->client_name,
            'client_id' => $client->id,
            'title' => 'Client Representative',
            'email' => $client->email,
            'phone' => $client->phone,
            'owner' => $client->owner ?? 'Jeota Media',
            'notes' => 'Generated from Client creation',
        ]);

        AuditLog::record('CREATE', "Registered corporate client '{$client->client_name}'", 'Client', $client->id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Client created successfully.',
            'data' => $client,
        ], 201);
    }

    public function show(Client $client): JsonResponse
    {
        $clientName = $client->client_name;

        $projects = Project::where('client', $clientName)
            ->with('tasks')
            ->latest()
            ->get();

        $invoices = Invoice::where('client', $clientName)
            ->latest()
            ->get();

        $quotes = Quote::where('client_id', $client->id)
            ->orWhere('recipient_name', 'like', "%{$clientName}%")
            ->orWhere('title', 'like', "%{$clientName}%")
            ->latest()
            ->get();

        $events = CalendarEvent::where('description', 'like', "%{$clientName}%")
            ->orWhere('title', 'like', "%{$clientName}%")
            ->latest('start_time')
            ->get();

        $activeCount = $projects->filter(function ($p) {
            $s = strtolower((string) ($p->status ?? ''));

            return $s !== 'completed' && $s !== 'delivered';
        })->count();

        $completedCount = $projects->filter(function ($p) {
            $s = strtolower((string) ($p->status ?? ''));

            return $s === 'completed' || $s === 'delivered';
        })->count();

        $totalProjectBudget = (float) $projects->sum('budget');
        $totalInvoiced = (float) $invoices->sum('amount');
        $totalPaid = (float) $invoices->filter(function ($i) {
            return strtolower((string) ($i->status ?? '')) === 'paid';
        })->sum('amount');
        $totalUnpaid = (float) $invoices->filter(function ($i) {
            return strtolower((string) ($i->status ?? '')) !== 'paid';
        })->sum('amount');
        $totalQuotes = (float) $quotes->sum('total_amount');

        // Build running balance statement ledger
        $ledgerEntries = collect();
        $runningBalance = 0;

        foreach ($invoices->sortBy('created_at') as $inv) {
            $isPaid = strtolower((string) ($inv->status ?? '')) === 'paid';
            $amt = (float) ($inv->amount ?? 0);
            $runningBalance += $amt;

            $ledgerEntries->push([
                'id' => 'inv_'.$inv->id,
                'date' => $inv->created_at?->toDateString() ?? $inv->due_date,
                'ref_no' => $inv->invoice_no ?? "INV-{$inv->id}",
                'type' => 'Invoice',
                'description' => $inv->type ?? 'Commercial Invoice',
                'debit' => $amt,
                'credit' => 0,
                'balance' => $runningBalance,
                'status' => $inv->status ?? 'Pending',
                'raw_id' => $inv->id,
                'entity' => 'invoice',
            ]);

            if ($isPaid) {
                $runningBalance -= $amt;
                $ledgerEntries->push([
                    'id' => 'pay_'.$inv->id,
                    'date' => $inv->updated_at?->toDateString() ?? $inv->created_at?->toDateString(),
                    'ref_no' => 'PAY-'.($inv->invoice_no ?? $inv->id),
                    'type' => 'Payment',
                    'description' => 'Payment received ('.($inv->method ?? 'Direct Transfer').')',
                    'debit' => 0,
                    'credit' => $amt,
                    'balance' => $runningBalance,
                    'status' => 'Settled',
                    'raw_id' => $inv->id,
                    'entity' => 'payment',
                ]);
            }
        }

        $responseData = array_merge($client->toArray(), [
            'client' => $client,
            'projects' => $projects,
            'invoices' => $invoices,
            'quotes' => $quotes,
            'events' => $events,
            'statement_ledger' => $ledgerEntries->values(),
            'stats' => [
                'total_projects' => $projects->count(),
                'active_projects' => $activeCount,
                'completed_projects' => $completedCount,
                'total_project_value' => $totalProjectBudget > 0 ? $totalProjectBudget : (float) $client->project_value,
                'total_invoiced' => $totalInvoiced,
                'total_paid' => $totalPaid,
                'total_unpaid' => $totalUnpaid,
                'total_quotes' => $totalQuotes,
                'quotes_count' => $quotes->count(),
                'closing_balance' => $runningBalance,
            ],
        ]);

        return response()->json($responseData);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'client_name' => 'sometimes|required|string|max:255',
            'client_type' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'owner' => 'nullable|string|max:255',
            'projects' => 'nullable|integer',
            'service' => 'nullable|string|max:255',
            'project_status' => 'nullable|string|max:100',
            'project_value' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $oldName = $client->client_name;
        $client->update($validated);

        // If client name changed, update corresponding projects and invoices to preserve link
        if (! empty($validated['client_name']) && $validated['client_name'] !== $oldName) {
            Project::where('client', $oldName)->update(['client' => $validated['client_name']]);
            Invoice::where('client', $oldName)->update(['client' => $validated['client_name']]);
        }

        // Keep associated contacts in sync
        Contact::where('client_id', $client->id)->update([
            'company_name' => $client->client_name,
            'owner' => $client->owner ?? 'Jeota Media',
        ]);

        AuditLog::record('UPDATE', "Updated client profile '{$client->client_name}'", 'Client', $client->id, $validated, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Client updated successfully.',
            'data' => $client,
        ]);
    }

    public function destroy(Request $request, Client $client): JsonResponse
    {
        $name = $client->client_name;
        $id = $client->id;

        // Clean up linked contacts for this client
        Contact::where('client_id', $client->id)->delete();

        $client->delete();

        AuditLog::record('DELETE', "Removed client '{$name}' from directory", 'Client', $id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Client removed successfully.',
        ]);
    }
}
