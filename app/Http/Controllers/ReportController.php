<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        [$startAt, $endAt, $period] = $this->resolvePeriod($request);

        $query = Transaction::with(['customer', 'cashier'])
            ->where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$startAt, $endAt]);

        $allowedSorts = ['transaction_date', 'transaction_number', 'subtotal', 'discount_amount', 'point_redeem_amount', 'total_price', 'points_earned', 'points_redeemed'];
        $sort = in_array($request->get('sort'), $allowedSorts, true) ? $request->get('sort') : 'transaction_date';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $perPage = in_array((int) $request->get('per_page'), [10,15,20,50,100], true) ? (int) $request->get('per_page') : 20;
        $transactions = (clone $query)->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        $summary = (clone $query)->selectRaw('
            COUNT(*) as total_transactions,
            COALESCE(SUM(total_price),0) as total_revenue,
            COALESCE(SUM(discount_amount),0) as total_discount,
            COALESCE(SUM(point_redeem_amount),0) as total_redeem,
            COALESCE(AVG(total_price),0) as avg_transaction
        ')->first();

        $dailyChart = DB::table('transactions')
            ->where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$startAt, $endAt])
            ->selectRaw('DATE(transaction_date) as date, SUM(total_price) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('reports.sales', compact('transactions', 'summary', 'dailyChart', 'startAt', 'endAt', 'period', 'sort', 'dir'));
    }

    public function profit(Request $request)
    {
        [$startAt, $endAt, $period] = $this->resolvePeriod($request);

        $allowedSorts = ['product_code', 'product_name', 'total_qty', 'total_revenue', 'total_hpp', 'gross_profit'];
        $sort = in_array($request->get('sort'), $allowedSorts, true) ? $request->get('sort') : 'gross_profit';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $profitData = DB::table('transaction_details as td')
            ->join('transactions as t', 't.id', '=', 'td.transaction_id')
            ->join('products as p', 'p.id', '=', 'td.product_id')
            ->where('t.payment_status', 'paid')
            ->whereBetween('t.transaction_date', [$startAt, $endAt])
            ->selectRaw('
                p.product_name,
                p.product_code,
                SUM(td.qty) as total_qty,
                SUM(td.subtotal - CASE WHEN t.subtotal > 0 THEN ((COALESCE(t.discount_amount,0) + COALESCE(t.point_redeem_amount,0)) * td.subtotal / t.subtotal) ELSE 0 END) as total_revenue,
                SUM(p.hpp * td.qty) as total_hpp,
                SUM((td.subtotal - CASE WHEN t.subtotal > 0 THEN ((COALESCE(t.discount_amount,0) + COALESCE(t.point_redeem_amount,0)) * td.subtotal / t.subtotal) ELSE 0 END) - (p.hpp * td.qty)) as gross_profit
            ')
            ->groupBy('p.id', 'p.product_name', 'p.product_code')
            ->orderBy($sort, $dir)
            ->paginate(in_array((int) $request->get('per_page'), [10,15,20,50,100], true) ? (int) $request->get('per_page') : 20)
            ->withQueryString();

        $totalProfit = DB::table('transaction_details as td')
            ->join('transactions as t', 't.id', '=', 'td.transaction_id')
            ->join('products as p', 'p.id', '=', 'td.product_id')
            ->where('t.payment_status', 'paid')
            ->whereBetween('t.transaction_date', [$startAt, $endAt])
            ->selectRaw('
                COALESCE(SUM(td.subtotal - CASE WHEN t.subtotal > 0 THEN ((COALESCE(t.discount_amount,0) + COALESCE(t.point_redeem_amount,0)) * td.subtotal / t.subtotal) ELSE 0 END),0) as total_revenue,
                COALESCE(SUM(p.hpp * td.qty),0) as total_hpp,
                COALESCE(SUM((td.subtotal - CASE WHEN t.subtotal > 0 THEN ((COALESCE(t.discount_amount,0) + COALESCE(t.point_redeem_amount,0)) * td.subtotal / t.subtotal) ELSE 0 END) - (p.hpp * td.qty)),0) as gross_profit
            ')
            ->first();

        return view('reports.profit', compact('profitData', 'totalProfit', 'startAt', 'endAt', 'period', 'sort', 'dir'));
    }

    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('action', 'like', "%{$request->search}%")
                  ->orWhere('detail', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sortBy = request('sort_by', 'created_at');
        $sortDir = request('sort_dir', 'desc');
        $allowedSorts = ['action', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('reports.activity_logs', compact('logs'));
    }

    private function resolvePeriod(Request $request): array
    {
        $period = $request->get('period', 'month');
        $now = now();

        return match ($period) {
            'hour' => [
                Carbon::parse(($request->date_from ?: $now->toDateString()) . ' ' . ($request->time_from ?: '00:00'))->startOfMinute(),
                Carbon::parse(($request->date_from ?: $now->toDateString()) . ' ' . ($request->time_to ?: '23:59'))->endOfMinute(),
                'hour',
            ],
            'day' => [
                Carbon::parse($request->date_from ?: $now->toDateString())->startOfDay(),
                Carbon::parse($request->date_to ?: ($request->date_from ?: $now->toDateString()))->endOfDay(),
                'day',
            ],
            'week' => [
                Carbon::parse($request->date_from ?: $now->copy()->startOfWeek()->toDateString())->startOfDay(),
                Carbon::parse($request->date_to ?: $now->copy()->endOfWeek()->toDateString())->endOfDay(),
                'week',
            ],
            'year' => [
                Carbon::parse($request->date_from ?: $now->copy()->startOfYear()->toDateString())->startOfDay(),
                Carbon::parse($request->date_to ?: $now->copy()->endOfYear()->toDateString())->endOfDay(),
                'year',
            ],
            'custom' => [
                Carbon::parse($request->date_from ?: $now->copy()->startOfMonth()->toDateString())->startOfDay(),
                Carbon::parse($request->date_to ?: $now->toDateString())->endOfDay(),
                'custom',
            ],
            default => [
                Carbon::parse($request->date_from ?: $now->copy()->startOfMonth()->toDateString())->startOfDay(),
                Carbon::parse($request->date_to ?: $now->toDateString())->endOfDay(),
                'month',
            ],
        };
    }
}
