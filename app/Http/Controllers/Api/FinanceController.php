<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\FinanceSetting;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;

class FinanceController extends Controller
{
    public function overview(): JsonResponse
    {
        $settingBf = FinanceSetting::where('key', 'brought_forward')->first();
        $broughtForward = $settingBf ? (float) $settingBf->numeric_value : 0.00;

        $invoices = Invoice::all();
        $expenses = Expense::all();

        $moneyIn = $invoices->where('status', 'Paid')->sum('amount');
        $moneyOut = $expenses->sum('amount');
        $unpaid = $invoices->where('status', '!=', 'Paid')->sum('amount');
        $overdueCount = $invoices->where('status', 'Overdue')->count();

        $balance = $broughtForward + $moneyIn - $moneyOut;
        $profit = $moneyIn - $moneyOut;

        // Build unified ledger
        $ledger = [];
        foreach ($invoices->where('status', 'Paid') as $inv) {
            $ledger[] = [
                'item' => "{$inv->invoice_no} — {$inv->client}",
                'type' => 'Invoice paid',
                'in' => (float) $inv->amount,
                'out' => null,
                'created_at' => $inv->created_at ? $inv->created_at->toIso8601String() : null,
            ];
        }
        foreach ($expenses as $exp) {
            $ledger[] = [
                'item' => $exp->name,
                'type' => $exp->category,
                'in' => null,
                'out' => (float) $exp->amount,
                'created_at' => $exp->created_at ? $exp->created_at->toIso8601String() : null,
            ];
        }

        return response()->json([
            'status' => 'success',
            'brought_forward' => (float) $broughtForward,
            'money_in' => (float) $moneyIn,
            'money_out' => (float) $moneyOut,
            'current_balance' => (float) $balance,
            'profit' => (float) $profit,
            'unpaid_total' => (float) $unpaid,
            'overdue_count' => $overdueCount,
            'ledger' => $ledger,
        ]);
    }
}
