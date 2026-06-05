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
        // Check active promo first
        $promo = Promotion::getActiveForProduct($productId);

        if ($promo && $this->promoAppliesToCustomer($promo, $customer)) {
            $baseDiscount = (float) $promo->discount_amount;
            $pointPromoDiscount = 0;
            $pointPromoCost = 0;

            if ($customer && $promo->can_redeem_with_points && (int) $promo->redeem_points_required > 0) {
                $required = (int) $promo->redeem_points_required;
                if ((float) $customer->point_balance >= $required) {
                    $pointPromoCost = $required;
                    $pointPromoDiscount = (float) $promo->redeem_discount_amount;
                }
            }

            $finalPrice = max(0, $sellingPrice - $baseDiscount - $pointPromoDiscount);

            return [
                'final_price'      => $finalPrice,
                'promo'            => $promo,
                'discount_percent' => 0,
                'discount_source'  => 'promo',
                'promo_redeem_points' => $pointPromoCost,
                'promo_redeem_amount' => $pointPromoDiscount,
            ];
        }

        // No promo — check member discount
        if ($customer) {
            $discountPercent = $this->getDiscount($customer);
            $finalPrice      = $sellingPrice * (1 - $discountPercent / 100);

            return [
                'final_price'      => round($finalPrice, 2),
                'promo'            => null,
                'discount_percent' => $discountPercent,
                'discount_source'  => 'member',
                'promo_redeem_points' => 0,
                'promo_redeem_amount' => 0,
            ];
        }

        // No promo, no member
        return [
            'final_price'      => $sellingPrice,
            'promo'            => null,
            'discount_percent' => 0,
            'discount_source'  => 'none',
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
        $customer->tier = $this->evaluateTier((float) $customer->total_accumulation);

        $customer->save();

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

    public function refreshRules(): void
    {
        $this->rule = MembershipRule::getCurrent();
    }

    public function getRule(): MembershipRule
    {
        return $this->rule;
    }
}
