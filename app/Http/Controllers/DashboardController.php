<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $today = now()->toDateString();

        // ---- Stats Cards ----
        $salesQuery = Transaction::where('payment_status', 'paid');

        $revenueToday      = (clone $salesQuery)->whereDate('transaction_date', $today)->sum('total_price');
        $transactionsToday = (clone $salesQuery)->whereDate('transaction_date', $today)->count();

        $revenueThisMonth = (clone $salesQuery)
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->sum('total_price');

        $totalCustomers = Customer::count();

        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->count();

        // ---- Chart: Revenue last 7 days ----
        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date          = now()->subDays($i)->toDateString();
            $chartLabels[] = now()->subDays($i)->format('d/m');
            $chartValues[] = (float) Transaction::where('payment_status', 'paid')
                ->whereDate('transaction_date', $date)
                ->sum('total_price');
        }

        // ---- Chart: Tier Composition ----
        $tierStats = Customer::selectRaw('tier, COUNT(*) as total')
            ->groupBy('tier')
            ->pluck('total', 'tier')
            ->toArray();

        // ---- Recent Transactions ----
        $recentTransactions = Transaction::with(['customer', 'cashier'])
            ->latest('transaction_date')
            ->limit(8)
            ->get();

        // ---- Low Stock Products ----
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->limit(6)
            ->get();

        // ---- Gross Profit (pemilik & admin only) ----
        $grossProfit = null;
        if ($user->isPemilik() || $user->isAdmin()) {
            $grossProfit = DB::table('transaction_details as td')
                ->join('transactions as t', 't.id', '=', 'td.transaction_id')
                ->join('products as p', 'p.id', '=', 'td.product_id')
                ->whereYear('t.transaction_date', now()->year)
                ->whereMonth('t.transaction_date', now()->month)
                ->where('t.payment_status', 'paid')
                ->selectRaw('SUM((td.final_unit_price - p.hpp) * td.qty) as profit')
                ->value('profit') ?? 0;
        }

        return view('dashboard.index', compact(
            'revenueToday',
            'transactionsToday',
            'revenueThisMonth',
            'totalCustomers',
            'lowStockCount',
            'chartLabels',
            'chartValues',
            'tierStats',
            'recentTransactions',
            'lowStockProducts',
            'grossProfit',
        ));
    }
}
