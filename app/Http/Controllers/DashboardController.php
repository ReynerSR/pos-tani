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
        
        if ($user->role === 'kasir') {
            $salesQuery->where('cashier_id', $user->id);
        }

        $revenueToday      = (clone $salesQuery)->whereDate('transaction_date', $today)->sum('total_price');
        $transactionsToday = (clone $salesQuery)->whereDate('transaction_date', $today)->count();

        $revenueThisMonth = (clone $salesQuery)
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->sum('total_price');

        $totalCustomers = Customer::count();

        $emptyStockCount = Product::where('is_active', true)
            ->where('stock', '<=', 0)
            ->count();

        $lowStockCount = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->count();

        $chartLabels = [];
        $chartValues = [];
        $chartDates = [];
        for ($i = 6; $i >= 0; $i--) {
            $date          = now()->subDays($i)->toDateString();
            $chartLabels[] = now()->subDays($i)->format('d/m');
            $chartDates[]  = $date;
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
        $recentTransactionsQuery = Transaction::with(['customer', 'cashier'])
            ->latest('transaction_date');
            
        if ($user->role === 'kasir') {
            $recentTransactionsQuery->where('cashier_id', $user->id)->whereDate('transaction_date', $today);
        }
            
        $recentTransactions = $recentTransactionsQuery->limit(8)->get();

        // ---- Low Stock Products ----
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->limit(6)
            ->get();

        // ---- Gross Profit (pemilik & admin only) ----
        $grossProfit = null;
        if ($user->isPemilik()) {
            $grossProfit = DB::table('transaction_details as td')
                ->join('transactions as t', 't.id', '=', 'td.transaction_id')
                ->join('products as p', 'p.id', '=', 'td.product_id')
                ->whereYear('t.transaction_date', now()->year)
                ->whereMonth('t.transaction_date', now()->month)
                ->where('t.payment_status', 'paid')
                ->selectRaw('SUM((td.final_unit_price - p.hpp) * td.qty) as profit')
                ->value('profit') ?? 0;
        }

        // ---- Trending Products ----
        $trendingLabels = [];
        $trendingValues = [];
        $trendingIds = [];
        if ($user->role === 'pemilik' || $user->role === 'admin') {
            $trending = DB::table('transaction_details as td')
                ->join('transactions as t', 't.id', '=', 'td.transaction_id')
                ->join('products as p', 'p.id', '=', 'td.product_id')
                ->where('t.payment_status', 'paid')
                ->whereMonth('t.transaction_date', now()->month)
                ->whereYear('t.transaction_date', now()->year)
                ->select('p.id', 'p.product_name', DB::raw('SUM(td.qty) as total_qty'))
                ->groupBy('p.id', 'p.product_name')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get();
            
            foreach ($trending as $item) {
                $trendingLabels[] = \Illuminate\Support\Str::limit($item->product_name, 15);
                $trendingValues[] = (int) $item->total_qty;
                $trendingIds[] = $item->id;
            }
        }

        $cashierPerformance = [];
        if (in_array($user->role, ['pemilik', 'admin'])) {
            $cashierPerformance = Transaction::where('payment_status', 'paid')
                ->whereDate('transaction_date', $today)
                ->join('users', 'users.id', '=', 'transactions.cashier_id')
                ->selectRaw('users.id, users.name, COUNT(transactions.id) as total_transactions, SUM(transactions.total_price) as total_revenue')
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->get();
        }

        return view('dashboard.index', compact(
            'revenueToday',
            'transactionsToday',
            'revenueThisMonth',
            'totalCustomers',
            'lowStockCount',
            'emptyStockCount',
            'chartLabels',
            'chartValues',
            'chartDates',
            'tierStats',
            'recentTransactions',
            'lowStockProducts',
            'grossProfit',
            'trendingLabels',
            'trendingValues',
            'trendingIds',
            'cashierPerformance'
        ));
    }
}
