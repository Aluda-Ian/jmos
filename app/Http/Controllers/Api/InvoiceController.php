<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Invoice::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_no' => 'required|string|unique:invoices,invoice_no',
            'client' => 'required|string',
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'method' => 'nullable|string',
            'etims' => 'nullable|boolean',
            'status' => 'nullable|in:Sent,Paid,Overdue',
            'due_date' => 'nullable|string',
        ]);

        $invoice = Invoice::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice created.',
            'data' => $invoice
        ], 201);
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'client' => 'sometimes|required|string',
            'type' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'method' => 'nullable|string',
            'etims' => 'nullable|boolean',
            'status' => 'nullable|in:Sent,Paid,Overdue',
            'due_date' => 'nullable|string',
        ]);

        $invoice->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice updated.',
            'data' => $invoice
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

        return response()->json([
            'status' => 'success',
            'message' => 'Payment recorded for ' . $invoice->invoice_no,
            'data' => $invoice
        ]);
    }
}
