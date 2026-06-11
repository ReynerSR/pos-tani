<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerTierHistory;
use App\Models\MembershipRule;
use App\Models\PointHistory;
use App\Models\Promotion;
use App\Models\Transaction;

class RuleBasedMembershipService
{
    private MembershipRule $rule;

    public function __construct()
    {
        $this->rule = MembershipRule::getCurrent();
    }

    /**
     * Get applicable discount percent for a customer tier.
     */
    public function getDiscount(Customer $customer): float
    {
        return $this->rule->getDiscountForTier($customer->tier);
    }

    /**
     * Calculate points earned from a transaction total.
     */
    public function calculatePoints(float $totalPrice): float
    {
        if ($this->rule->point_per_nominal <= 0) {
            return 0;
        }

        return floor($totalPrice / $this->rule->point_per_nominal);
    }

    /**
     * Resolve the final unit price for a product in the cart.
     *
     * RULE:
     *   IF produk memiliki promo aktif hari ini
     *     THEN harga_akhir = harga_jual - promo_nominal
     *          diskon_member = 0 (promo dan diskon member tidak bisa bersamaan)
     *   ELSE IF pelanggan adalah member
     *     THEN harga_akhir = harga_jual * (1 - diskon_member%)
     *   ELSE
     *     harga_akhir = harga_jual
     *
     * @return array{final_price: float, promo: ?Promotion, discount_percent: float, discount_source: string}
     */
    public function resolvePricing(int $productId, float $sellingPrice, ?Customer $customer): array
    {
        $finalPrice = $sellingPrice;
        $activePromo = Promotion::getActiveForProduct($productId);
        $appliedPromo = null;
        $discountSource = 'none';

        if ($activePromo && $this->promoAppliesToCustomer($activePromo, $customer)) {
            $baseDiscount = (float) $activePromo->discount_amount;
            $finalPrice = max(0, $finalPrice - $baseDiscount);
            $appliedPromo = $activePromo;
            $discountSource = 'promo';
        }

        if ($customer) {
            $discountPercent = $this->getDiscount($customer);
            if ($discountPercent > 0) {
                if ($discountSource === 'promo') {
                    $discountSource = 'promo+member';
                } else {
                    $discountSource = 'member';
                }
            }
        }

        return [
            'final_price'         => round($finalPrice, 2),
            'promo'               => $appliedPromo,
            'discount_percent'    => $customer ? $this->getDiscount($customer) : 0,
            'discount_source'     => $discountSource,
            'promo_redeem_points' => 0,
            'promo_redeem_amount' => 0,
        ];
    }

    private function promoAppliesToCustomer(Promotion $promo, ?Customer $customer): bool
    {
        $tiers = $promo->eligible_tiers ?: [];
        if (empty($tiers)) {
            return true;
        }

        if (! $customer) {
            return false;
        }

        return in_array($customer->tier, $tiers, true);
    }

    /**
     * After a transaction is saved, apply membership rules.
     */
    public function applyAfterTransaction(Transaction $transaction): void
    {
        if (! $transaction->customer_id) {
            return;
        }

        $customer = Customer::lockForUpdate()->find($transaction->customer_id);
        if (! $customer) {
            return;
        }

        $transactionTotal = (float) $transaction->total_price;
        $pointsRedeemed  = (float) ($transaction->points_redeemed ?? 0);
        $pointsEarned    = (float) ($transaction->points_earned ?? 0);

        if ($pointsEarned <= 0) {
            $pointsEarned = $this->calculatePoints($transactionTotal);
        }

        if ($pointsRedeemed > 0 && (float) $customer->point_balance < $pointsRedeemed) {
            throw new \RuntimeException('Saldo poin member tidak mencukupi untuk redeem.');
        }

        // Rule 1: Update accumulation from actual paid transaction value after discounts/redeem.
        $customer->total_accumulation += $transactionTotal;

        // Rule 2: Redeem point first, then award new points from this transaction.
        $customer->point_balance = max(0, (float) $customer->point_balance - $pointsRedeemed + $pointsEarned);

        // Rule 3: Evaluate Tier (IF-THEN) and keep an audit history.
        $oldTier = $customer->tier;
        $oldTotal = (float) ($customer->getOriginal('total_accumulation') ?? 0);
        $evaluatedTier = $this->evaluateTier((float) $customer->total_accumulation);

        // Mencegah penurunan tier (downgrade) jika member diset manual ke tier tinggi
        // tetapi akumulasinya belum cukup. Hanya naik tier yang diperbolehkan otomatis.
        if ($this->getTierLevel($evaluatedTier) > $this->getTierLevel($oldTier)) {
            $customer->tier = $evaluatedTier;
        }

        $customer->save();

        // Simpan point balance customer setelah transaksi ke record transaksi
        $transaction->customer_point_balance = $customer->point_balance;
        $transaction->save();

        if ($oldTier !== $customer->tier) {
            CustomerTierHistory::create([
                'customer_id' => $customer->id,
                'transaction_id' => $transaction->id,
                'changed_by' => auth()->id(),
                'old_tier' => $oldTier,
                'new_tier' => $customer->tier,
                'old_total_accumulation' => $oldTotal,
                'new_total_accumulation' => (float) $customer->total_accumulation,
                'source' => 'transaction',
                'notes' => "Tier berubah otomatis dari {$oldTier} ke {$customer->tier} karena transaksi {$transaction->transaction_number}.",
            ]);
        }

        // Rule 4: Record point histories. Negative = poin keluar/redeem, positive = poin masuk.
        if ($pointsRedeemed > 0) {
            PointHistory::create([
                'customer_id'    => $customer->id,
                'transaction_id' => $transaction->id,
                'points_earned'  => -1 * $pointsRedeemed,
                'description'    => "Redeem poin untuk transaksi {$transaction->transaction_number}",
            ]);
        }

        if ($pointsEarned > 0) {
            PointHistory::create([
                'customer_id'    => $customer->id,
                'transaction_id' => $transaction->id,
                'points_earned'  => $pointsEarned,
                'description'    => "Poin dari transaksi {$transaction->transaction_number}",
            ]);
        }
    }

    /**
     * IF-THEN tier evaluation.
     */
    public function evaluateTier(float $totalAccumulation): string
    {
        if ($totalAccumulation >= (float) $this->rule->tier_gold_min) {
            return 'gold';
        }

        if ($totalAccumulation >= (float) $this->rule->tier_silver_min) {
            return 'silver';
        }

        return 'bronze';
    }

    private function getTierLevel(string $tier): int
    {
        return match (strtolower($tier)) {
            'gold'   => 3,
            'silver' => 2,
            default  => 1,
        };
    }

    public function refreshRules(): void
    {
        $this->rule = MembershipRule::getCurrent();
    }

    public function getRule(): MembershipRule
    {
        return $this->rule;
    }
}
