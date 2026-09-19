<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Invoice::latest()->get());
    }

    public function nextNumber(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'invoice_no' => Invoice::nextInvoiceNo(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_no' => 'nullable|string|unique:invoices,invoice_no',
            'client' => 'required|string',
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'method' => 'nullable|string',
            'etims' => 'nullable|boolean',
            'status' => 'nullable|in:Sent,Paid,Overdue',
            'due_date' => 'nullable|string',
        ]);

        if (empty($validated['invoice_no'])) {
            $validated['invoice_no'] = Invoice::nextInvoiceNo();
        }

        $invoice = Invoice::create($validated);

        AuditLog::record('CREATE', "Created invoice {$invoice->invoice_no} for {$invoice->client} (KES ".number_format((float) $invoice->amount, 2).')', 'Invoice', $invoice->id, $validated, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice created.',
            'data' => $invoice,
        ], 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $invoice,
        ]);
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'invoice_no' => 'sometimes|required|string|unique:invoices,invoice_no,'.$invoice->id,
            'client' => 'sometimes|required|string',
            'type' => 'nullable|string',
            'amount' => 'sometimes|required|numeric',
            'method' => 'nullable|string',
            'etims' => 'nullable|boolean',
            'status' => 'nullable|in:Sent,Paid,Overdue',
            'due_date' => 'nullable|string',
        ]);

        $invoice->update($validated);

        AuditLog::record('UPDATE', "Updated invoice {$invoice->invoice_no}", 'Invoice', $invoice->id, $validated, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice '.$invoice->invoice_no.' updated.',
            'data' => $invoice,
        ]);
    }

    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        $invoiceNo = $invoice->invoice_no;
        $id = $invoice->id;
        $invoice->delete();

        AuditLog::record('DELETE', "Removed invoice {$invoiceNo}", 'Invoice', $id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice '.$invoiceNo.' deleted.',
        ]);
    }

    public function pay(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'method' => 'nullable|string',
        ]);

        $invoice->update([
            'status' => 'Paid',
            'method' => $validated['method'] ?? ($invoice->method ?: 'M-Pesa'),
        ]);

        AuditLog::record('PAYMENT', "Recorded payment for invoice {$invoice->invoice_no} (Method: {$invoice->method})", 'Invoice', $invoice->id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment recorded for '.$invoice->invoice_no,
            'data' => $invoice,
        ]);
    }

    /**
     * Dispatch branded invoice / payment reminder email to client.
     */
    public function sendReminder(Request $request, Invoice $invoice): JsonResponse
    {
        $recipientEmail = $request->input('email');

        if (empty($recipientEmail)) {
            // Find client email from Clients table
            $client = Client::where('client_name', $invoice->client)->first();
            $recipientEmail = $client?->email;
        }

        if (empty($recipientEmail) || ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Valid recipient email address is required.',
            ], 422);
        }

        $sent = NotificationService::sendInvoiceReminder([
            'clientName' => $invoice->client,
            'invoiceNo' => $invoice->invoice_no,
            'invoiceType' => $invoice->type,
            'amount' => (float) $invoice->amount,
            'dueDate' => $invoice->due_date ?? 'Immediate',
        ], $recipientEmail);

        if ($sent) {
            AuditLog::record('EMAIL', "Dispatched invoice reminder for {$invoice->invoice_no} to {$recipientEmail}", 'Invoice', $invoice->id, [], $request);

            return response()->json([
                'status' => 'success',
                'message' => "Invoice reminder dispatched to {$recipientEmail}.",
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to dispatch email. Please check SMTP settings.',
        ], 500);
    }
}
