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

        $moneyIn = (float) Invoice::where('status', 'Paid')->sum('amount');
        $moneyOut = (float) Expense::sum('amount');
        $unpaid = (float) Invoice::where('status', '!=', 'Paid')->sum('amount');
        $overdueCount = Invoice::where('status', 'Overdue')->count();

        $balance = $broughtForward + $moneyIn - $moneyOut;
        $profit = $moneyIn - $moneyOut;

        // Build unified ledger
        $paidInvoices = Invoice::where('status', 'Paid')->latest('created_at')->get();
        $allExpenses = Expense::latest('created_at')->get();

        $ledger = [];
        foreach ($paidInvoices as $inv) {
            $ledger[] = [
                'item' => "{$inv->invoice_no} — {$inv->client}",
                'type' => 'Invoice paid',
                'in' => (float) $inv->amount,
                'out' => null,
                'created_at' => $inv->created_at ? $inv->created_at->toIso8601String() : null,
                'timestamp' => $inv->created_at ? $inv->created_at->timestamp : 0,
            ];
        }
        foreach ($allExpenses as $exp) {
            $ledger[] = [
                'item' => $exp->name,
                'type' => $exp->category,
                'in' => null,
                'out' => (float) $exp->amount,
                'created_at' => $exp->created_at ? $exp->created_at->toIso8601String() : null,
                'timestamp' => $exp->created_at ? $exp->created_at->timestamp : 0,
            ];
        }

        // Sort unified ledger chronologically (newest first)
        usort($ledger, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        // Strip internal sort key
        $ledger = array_map(function ($item) {
            unset($item['timestamp']);

            return $item;
        }, $ledger);

        return response()->json([
            'status' => 'success',
            'brought_forward' => (float) $broughtForward,
            'money_in' => (float) $moneyIn,
            'money_out' => (float) $moneyOut,
            'current_balance' => (float) $balance,
            'profit' => (float) $profit,
            'unpaid_total' => (float) $unpaid,
            'overdue_count' => $overdueCount,
            'ledger' => array_values($ledger),
        ]);
    }
}
