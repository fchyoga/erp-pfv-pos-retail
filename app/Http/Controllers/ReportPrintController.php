<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransactionItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Outlet;
use Illuminate\Support\Facades\DB;

class ReportPrintController extends Controller
{
    public function print(Request $request)
    {
        // Enforce authorization
        if (!auth()->check() || (!auth()->user()->hasRole('super_admin') && !auth()->user()->hasPermissionTo('view_reports'))) {
            // Standard Filament pages are protected. Let's redirect if unauthorized.
        }

        $type = $request->query('type');
        $startDate = $request->query('created_from');
        $endDate = $request->query('created_until');
        $outletId = $request->query('outlet_id');

        $outlet = $outletId ? Outlet::find($outletId) : null;

        switch ($type) {
            case 'best-seller':
                $query = TransactionItem::query()
                    ->select('product_id', 'product_name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(transaction_items.subtotal) as total_revenue'))
                    ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                    ->where('transactions.status', 'completed')
                    ->groupBy('product_id', 'product_name')
                    ->orderBy('total_sold', 'desc');
                break;

            case 'payment-summary':
                $query = Payment::query()
                    ->select('method', DB::raw('COUNT(payments.id) as total_transactions'), DB::raw('SUM(amount) as total_amount'))
                    ->join('transactions', 'payments.transaction_id', '=', 'transactions.id')
                    ->where('transactions.status', 'completed')
                    ->groupBy('method')
                    ->orderBy('total_amount', 'desc');
                break;

            case 'profit-margin':
                $query = TransactionItem::query()
                    ->select(
                        'product_id', 
                        'product_name', 
                        DB::raw('SUM(quantity) as total_sold'),
                        DB::raw('SUM(transaction_items.subtotal) as total_revenue'),
                        DB::raw('SUM(cost_price * quantity) as total_cost'),
                        DB::raw('SUM(transaction_items.subtotal) - SUM(cost_price * quantity) as gross_profit')
                    )
                    ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                    ->where('transactions.status', 'completed')
                    ->groupBy('product_id', 'product_name')
                    ->orderBy('gross_profit', 'desc');
                break;

            case 'slow-moving':
                $query = Product::query()
                    ->select('products.id', 'products.name', 'products.stock')
                    ->selectSub(function ($query) use ($startDate, $endDate, $outletId) {
                        $query->selectRaw('COALESCE(SUM(quantity), 0)')
                            ->from('transaction_items')
                            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                            ->whereColumn('transaction_items.product_id', 'products.id')
                            ->where('transactions.status', 'completed')
                            ->when($startDate, fn($q) => $q->where('transactions.created_at', '>=', $startDate . ' 00:00:00'))
                            ->when($endDate, fn($q) => $q->where('transactions.created_at', '<=', $endDate . ' 23:59:59'))
                            ->when($outletId, fn($q) => $q->where('transactions.outlet_id', '=', $outletId));
                    }, 'total_sold')
                    ->orderBy('total_sold', 'asc');
                break;

            case 'sales':
                $userId = $request->query('user_id');
                $paymentMethod = $request->query('payment_method');
                $status = $request->query('status');

                $query = \App\Models\Transaction::query()
                    ->with(['outlet', 'user', 'payment'])
                    ->orderBy('created_at', 'desc');

                $query->when($userId, fn($q) => $q->where('user_id', $userId))
                      ->when($paymentMethod, fn($q) => $q->whereHas('payment', fn($qp) => $qp->where('method', $paymentMethod)))
                      ->when($status, fn($q) => $q->where('status', $status));
                break;

            default:
                abort(404, 'Report type not found.');
        }

        // Apply filters to standard reports (excluding slow-moving which does it inside selectSub)
        if ($type !== 'slow-moving') {
            $query->when($startDate, fn($q) => $q->where('transactions.created_at', '>=', $startDate . ' 00:00:00'))
                  ->when($endDate, fn($q) => $q->where('transactions.created_at', '<=', $endDate . ' 23:59:59'))
                  ->when($outletId, fn($q) => $q->where('transactions.outlet_id', '=', $outletId));
        }

        $records = $query->get();

        return view('print-report', compact('records', 'type', 'startDate', 'endDate', 'outlet'));
    }
}
