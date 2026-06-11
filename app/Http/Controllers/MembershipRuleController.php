<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\MembershipRule;
use Illuminate\Http\Request;

class MembershipRuleController extends Controller
{
    public function index()
    {
        $rule = MembershipRule::getCurrent();
        $logs = ActivityLog::with('user')
            ->where('action', 'UPDATE_MEMBERSHIP_RULE')
            ->latest()
            ->limit(10)
            ->get();

        return view('membership.index', compact('rule', 'logs'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'tier_silver_min'   => 'required|numeric|min:0',
            'tier_gold_min'     => 'required|numeric|min:0|gt:tier_silver_min',
            'point_per_nominal' => 'required|integer|min:100',
            'redeem_point_value' => 'required|numeric|min:1',
            'minimum_redeem_points' => 'required|numeric|min:0',
            'max_redeem_percent' => 'required|numeric|min:1|max:100',
            'discount_bronze'   => 'required|numeric|min:0|max:100',
            'discount_silver'   => 'required|numeric|min:0|max:100',
            'discount_gold'     => 'required|numeric|min:0|max:100',
            'redeem_multiple'   => 'required|integer|min:1',
        ], [
            'tier_gold_min.gt' => 'Batas Gold harus lebih besar dari batas Silver.',
        ]);

        $data['updated_by'] = auth()->id();

        $rule = MembershipRule::getCurrent();
        $rule->update($data);

        ActivityLog::record(
            'UPDATE_MEMBERSHIP_RULE',
            "Memperbarui aturan membership — Silver: Rp" . number_format($data['tier_silver_min']) .
            " | Gold: Rp" . number_format($data['tier_gold_min']) .
            " | Diskon Bronze: {$data['discount_bronze']}% | Silver: {$data['discount_silver']}% | Gold: {$data['discount_gold']}%" .
            " | Redeem: 1 poin = Rp" . number_format($data['redeem_point_value']) .
            " | Min: {$data['minimum_redeem_points']} poin | Maks: {$data['max_redeem_percent']}% transaksi | Kelipatan: {$data['redeem_multiple']} poin"
        );

        return redirect()->route('membership.index')
            ->with('success', 'Konfigurasi aturan membership berhasil diperbarui.');
    }
}
