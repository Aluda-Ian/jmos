<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Quote;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class QuoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Quote::with(['lead', 'client', 'deal', 'convertedInvoice'])->latest();

        if ($request->filled('search')) {
            $s = $request->string('search')->trim();
            $query->where(function ($q) use ($s) {
                $q->where('quote_number', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%")
                    ->orWhere('recipient_name', 'like', "%{$s}%")
                    ->orWhere('recipient_email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('lead_id')) {
            $query->where('lead_id', $request->integer('lead_id'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'client_id' => 'nullable|exists:clients,id',
            'deal_id' => 'nullable|exists:deals,id',
            'recipient_name' => 'required|string|max:255',
            'recipient_email' => 'nullable|email|max:255',
            'recipient_phone' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'items' => 'nullable|array',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.amount' => 'nullable|numeric',
            'subtotal' => 'nullable|numeric',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'validity_days' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:Draft,Sent,Negotiating,Accepted,Invoiced,Declined',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        $validated['quote_number'] = Quote::nextQuoteNumber();
        $validated['created_by'] = auth('sanctum')->user()?->name ?? 'Barny Kiome';

        if (empty($validated['items'])) {
            $validated['items'] = [
                [
                    'description' => $validated['title'],
                    'quantity' => 1,
                    'rate' => $validated['total_amount'],
                    'amount' => $validated['total_amount'],
                ],
            ];
        }

        $quote = Quote::create($validated);

        AuditLog::record('CREATE', "Created Quotation '{$quote->quote_number}' ({$quote->title}) for {$quote->recipient_name}", 'Quote', $quote->id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => "Quotation {$quote->quote_number} generated successfully.",
            'data' => $quote->load(['lead', 'client', 'deal']),
        ], 201);
    }

    public function show(Quote $quote): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $quote->load(['lead', 'client', 'deal', 'convertedInvoice']),
        ]);
    }

    public function update(Request $request, Quote $quote): JsonResponse
    {
        $validated = $request->validate([
            'recipient_name' => 'sometimes|required|string|max:255',
            'recipient_email' => 'nullable|email|max:255',
            'recipient_phone' => 'nullable|string|max:50',
            'title' => 'sometimes|required|string|max:255',
            'items' => 'nullable|array',
            'subtotal' => 'nullable|numeric',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'validity_days' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:Draft,Sent,Negotiating,Accepted,Invoiced,Declined',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        $quote->update($validated);

        AuditLog::record('UPDATE', "Updated Quotation '{$quote->quote_number}'", 'Quote', $quote->id, $validated, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Quotation updated successfully.',
            'data' => $quote->fresh(['lead', 'client', 'deal', 'convertedInvoice']),
        ]);
    }

    public function destroy(Quote $quote): JsonResponse
    {
        $num = $quote->quote_number;
        $id = $quote->id;
        $quote->delete();

        AuditLog::record('DELETE', "Removed Quotation '{$num}'", 'Quote', $id);

        return response()->json([
            'status' => 'success',
            'message' => "Quotation {$num} deleted.",
        ]);
    }

    /**
     * Send Quotation directly to Client/Lead Email via SMTP
     */
    public function sendEmail(Request $request, Quote $quote): JsonResponse
    {
        $email = $request->input('email', $quote->recipient_email);

        if (empty($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Recipient email address is required to dispatch quotation.',
            ], 422);
        }

        try {
            NotificationService::applySmtpSettings();

            $itemsHtml = '';
            foreach ($quote->items ?? [] as $item) {
                $desc = htmlspecialchars($item['description'] ?? '');
                $qty = $item['quantity'] ?? 1;
                $rate = number_format((float) ($item['rate'] ?? 0), 2);
                $amt = number_format((float) ($item['amount'] ?? 0), 2);
                $itemsHtml .= "<tr><td style='padding:8px 10px;border-bottom:1px solid #eee;'>{$desc}</td><td style='padding:8px 10px;border-bottom:1px solid #eee;text-align:center;'>{$qty}</td><td style='padding:8px 10px;border-bottom:1px solid #eee;text-align:right;'>KES {$rate}</td><td style='padding:8px 10px;border-bottom:1px solid #eee;text-align:right;font-weight:600;'>KES {$amt}</td></tr>";
            }

            $totalFormatted = number_format((float) $quote->total_amount, 2);
            $validUntil = now()->addDays($quote->validity_days)->format('M d, Y');
            $notesFormatted = ! empty($quote->notes) ? "<p style='margin-top:15px;color:#555;font-size:13px;'><b>Scope & Deliverables:</b><br>".nl2br(htmlspecialchars($quote->notes)).'</p>' : '';

            $htmlContent = "
            <div style='font-family:Inter,Arial,sans-serif;max-width:620px;margin:0 auto;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;'>
                <div style='background:#C52523;color:#ffffff;padding:24px 28px;'>
                    <h2 style='margin:0;font-size:22px;letter-spacing:0.5px;'>JEOTA MEDIA</h2>
                    <p style='margin:4px 0 0;font-size:13px;opacity:0.9;'>OFFICIAL COMMERCIAL QUOTATION</p>
                </div>
                <div style='padding:24px 28px;'>
                    <p style='font-size:15px;color:#1e293b;margin:0 0 16px;'>Dear <b>".htmlspecialchars($quote->recipient_name)."</b>,</p>
                    <p style='font-size:13.5px;color:#475569;line-height:1.6;'>Thank you for your interest in partnering with Jeota Media. Please review our official production quotation for <b>".htmlspecialchars($quote->title)."</b> below:</p>
                    
                    <div style='background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin:18px 0;'>
                        <div style='font-size:12px;color:#64748b;'>Quote Number: <b style='color:#0f172a;'>".htmlspecialchars($quote->quote_number)."</b></div>
                        <div style='font-size:12px;color:#64748b;margin-top:4px;'>Valid Until: <b style='color:#0f172a;'>{$validUntil}</b></div>
                    </div>

                    <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;'>
                        <thead>
                            <tr style='background:#f1f5f9;color:#475569;'>
                                <th style='padding:8px 10px;text-align:left;'>Item / Deliverable</th>
                                <th style='padding:8px 10px;text-align:center;'>Qty</th>
                                <th style='padding:8px 10px;text-align:right;'>Rate</th>
                                <th style='padding:8px 10px;text-align:right;'>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$itemsHtml}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan='3' style='padding:12px 10px;text-align:right;font-weight:700;font-size:14px;'>Total Proposal Value:</td>
                                <td style='padding:12px 10px;text-align:right;font-weight:700;font-size:15px;color:#C52523;'>KES {$totalFormatted}</td>
                            </tr>
                        </tfoot>
                    </table>

                    {$notesFormatted}

                    <div style='margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;'>
                        <p style='margin:0 0 6px;'><b>Payment Terms:</b> 60% production commencement deposit upon contract signing, balance upon final delivery approval.</p>
                        <p style='margin:0;'>For questions or customized adjustments, reply directly to this email or call <a href='tel:+254712345678' style='color:#C52523;'>+254 712 345 678</a>.</p>
                    </div>
                </div>
                <div style='background:#f8fafc;padding:12px 28px;text-align:center;font-size:11px;color:#94a3b8;border-top:1px solid #e2e8f0;'>
                    JMOS · Jeota Media Operating System · Nairobi, Kenya · <a href='https://jeotamedia.co.ke' style='color:#64748b;'>jeotamedia.co.ke</a>
                </div>
            </div>
            ";

            Mail::html($htmlContent, function ($message) use ($email, $quote) {
                $message->to($email)
                    ->subject("Commercial Quotation: {$quote->quote_number} — {$quote->title} (Jeota Media)");
            });

            $quote->update([
                'status' => 'Sent',
                'sent_at' => now(),
            ]);

            AuditLog::record('EMAIL', "Dispatched Quotation {$quote->quote_number} to {$email}", 'Quote', $quote->id);

            return response()->json([
                'status' => 'success',
                'message' => "Quotation {$quote->quote_number} dispatched to {$email}.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email dispatch failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate WhatsApp direct link and formatted message
     */
    public function getWhatsAppLink(Quote $quote): JsonResponse
    {
        $phone = preg_replace('/[^0-9]/', '', (string) ($quote->recipient_phone ?? ''));
        if (str_starts_with($phone, '0')) {
            $phone = '254'.substr($phone, 1);
        } elseif (str_starts_with($phone, '7')) {
            $phone = '254'.$phone;
        }

        $itemsSummary = '';
        foreach ($quote->items ?? [] as $i) {
            $itemsSummary .= '• '.($i['description'] ?? '').' (Qty: '.($i['quantity'] ?? 1).') - KES '.number_format((float) ($i['amount'] ?? 0), 2)."\n";
        }

        $totalFormatted = number_format((float) $quote->total_amount, 2);
        $validUntil = now()->addDays($quote->validity_days)->format('M d, Y');

        $message = "Hello *{$quote->recipient_name}*,\n\n"
            ."Here is your official commercial proposal from *Jeota Media*:\n\n"
            ."📄 *Quote Ref:* {$quote->quote_number}\n"
            ."🎯 *Project:* {$quote->title}\n"
            ."📅 *Valid Until:* {$validUntil}\n\n"
            ."*Deliverables & Breakdown:*\n"
            .$itemsSummary."\n"
            ."💰 *Total Quotation Value:* KES {$totalFormatted}\n"
            ."💳 *Payment Terms:* 60% Deposit on Kickoff, 40% on Final Delivery Master.\n\n"
            ."Let us know if you would like to proceed or schedule a discovery alignment call!\n\n"
            ."Best regards,\n"
            ."*Jeota Media Production Team*\n"
            .'https://jeotamedia.co.ke';

        $encodedText = urlencode($message);
        $whatsappUrl = "https://api.whatsapp.com/send?phone={$phone}&text={$encodedText}";

        $quote->update([
            'status' => $quote->status === 'Draft' ? 'Sent' : $quote->status,
            'sent_at' => now(),
        ]);

        AuditLog::record('WHATSAPP', "Generated WhatsApp quote dispatch for {$quote->quote_number}", 'Quote', $quote->id);

        return response()->json([
            'status' => 'success',
            'whatsapp_url' => $whatsappUrl,
            'message' => $message,
        ]);
    }

    /**
     * Quote-to-Invoice Upgrade Engine:
     * Converts / Upgrades negotiated quotation into official invoice in JMOS.
     */
    public function upgradeToInvoice(Request $request, Quote $quote): JsonResponse
    {
        $validated = $request->validate([
            'invoice_type' => 'required|string', // Deposit 60%, Full Payment 100%, Milestone 50%, Custom
            'amount' => 'required|numeric|min:1',
            'due_date' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $nextNo = Invoice::nextInvoiceNo();
        $clientName = $quote->client ? $quote->client->client_name : $quote->recipient_name;

        $invoice = Invoice::create([
            'invoice_no' => $nextNo,
            'client' => $clientName,
            'type' => $validated['invoice_type'],
            'amount' => $validated['amount'],
            'method' => null,
            'etims' => false,
            'status' => 'Sent',
            'due_date' => ! empty($validated['due_date']) ? $validated['due_date'] : now()->addDays(7)->format('M d'),
        ]);

        $quote->update([
            'status' => 'Invoiced',
            'converted_invoice_id' => $invoice->id,
        ]);

        AuditLog::record(
            'UPGRADE',
            "Upgraded Quotation {$quote->quote_number} to official Invoice {$invoice->invoice_no} (KES ".number_format((float) $invoice->amount, 2).')',
            'Invoice',
            $invoice->id,
            [],
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => "Quotation {$quote->quote_number} upgraded to Invoice {$invoice->invoice_no} successfully.",
            'quote' => $quote->fresh(['convertedInvoice']),
            'invoice' => $invoice,
        ]);
    }
}
