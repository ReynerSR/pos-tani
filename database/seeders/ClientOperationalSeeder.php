<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\MembershipRule;
use App\Models\PointHistory;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\StockTransferDetail;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ClientOperationalSeeder extends Seeder
{
    /**
     * Seeder data client UD. Tani Agung Ngawi.
     *
     * Cakupan:
     * - Menghapus data operasional/master dummy: produk, customer, supplier,
     *   transaksi, pembelian, promo, stock opname, transfer stok, stok gudang, log.
     * - Tidak menghapus akun login, aturan membership, dan struktur master gudang.
     * - Mengisi master produk/customer/supplier dari data client.
     * - Membuat simulasi operasional dari 25 Mei 2026 sampai 03 Juni 2026.
     *
     * Catatan parsing data toko.zip: datasets obat2.json: recovered 22 valid rows; skipped incomplete tail.
     */
    private string $startDate = '2026-05-25';
    private string $endDate = '2026-06-03';

    public function run(): void
    {
        mt_srand(25052026);

        Model::unguarded(function () {
            $this->resetOperationalData();

            $users = $this->ensureBaseUsers();
            $rule = $this->ensureMembershipRule($users['owner']->id);
            $warehouses = $this->ensureWarehouses();

            $suppliers = $this->seedSuppliers();
            $products = $this->seedProducts($warehouses);
            $customers = $this->seedCustomers();
            $this->seedPromotions($products, $users['owner']->id);

            $stock = $this->buildStockMap();
            $this->seedPurchases($products, $suppliers, $warehouses, $users, $stock);
            $this->seedTransfers($products, $warehouses, $users, $stock);
            $this->seedTransactions($products, $customers, $warehouses, $users, $rule, $stock);
            $this->seedStockOpname($products, $warehouses, $users, $stock);

            $this->syncFinalProductAndWarehouseStocks($products, $stock, $warehouses);

            $this->insertLog($users['admin']->id, 'SEED_CLIENT_DATA', 'Data dummy operasional diganti dengan data client dan transaksi berjalan 25 Mei - 03 Juni 2026.', Carbon::parse($this->endDate . ' 17:20:00'));
        });
    }

    private function resetOperationalData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ([
            'stock_transfer_details',
            'stock_transfers',
            'stock_adjustments',
            'point_history',
            'transaction_details',
            'transactions',
            'purchase_details',
            'purchases',
            'promotions',
            'warehouse_stocks',
            'products',
            'customers',
            'suppliers',
            'activity_logs',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function ensureBaseUsers(): array
    {
        $owner = User::firstOrCreate(
            ['username' => 'pemilik'],
            [
                'name' => 'Sianny Soesanto',
                'email' => 'pemilik@taniagung.com',
                'password' => Hash::make('pemilik123'),
                'role' => 'pemilik',
                'is_active' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Operasional',
                'email' => 'admin@taniagung.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $cashier = User::firstOrCreate(
            ['username' => 'kasir1'],
            [
                'name' => 'Kasir 1',
                'email' => 'kasir1@taniagung.com',
                'password' => Hash::make('kasir123'),
                'role' => 'kasir',
                'is_active' => true,
            ]
        );

        return ['owner' => $owner, 'admin' => $admin, 'cashier' => $cashier];
    }

    private function ensureMembershipRule(int $ownerId): MembershipRule
    {
        return MembershipRule::query()->latest('id')->first() ?: MembershipRule::create([
            'tier_silver_min' => 5000000,
            'tier_gold_min' => 15000000,
            'point_per_nominal' => 1000,
            'redeem_point_value' => 100,
            'minimum_redeem_points' => 100,
            'max_redeem_percent' => 50,
            'discount_bronze' => 0,
            'discount_silver' => 3,
            'discount_gold' => 5,
            'updated_by' => $ownerId,
        ]);
    }

    private function ensureWarehouses(): array
    {
        $store = Warehouse::query()->where('is_store', true)->where('is_active', true)->first();
        if (! $store) {
            $store = Warehouse::updateOrCreate(
                ['code' => 'TOKO'],
                ['name' => 'Toko Utama', 'location' => 'Area penjualan utama', 'is_store' => true, 'is_active' => true]
            );
        }

        $mainWarehouse = Warehouse::query()
            ->where('is_store', false)
            ->where('is_active', true)
            ->first();

        if (! $mainWarehouse) {
            $mainWarehouse = Warehouse::updateOrCreate(
                ['code' => 'GDG'],
                ['name' => 'Gudang Belakang', 'location' => 'Gudang penyimpanan stok utama', 'is_store' => false, 'is_active' => true]
            );
        }

        $backupWarehouse = Warehouse::query()
            ->where('is_store', false)
            ->where('is_active', true)
            ->where('id', '!=', $mainWarehouse->id)
            ->first();

        if (! $backupWarehouse) {
            $backupWarehouse = Warehouse::updateOrCreate(
                ['code' => 'GDG-2'],
                ['name' => 'Gudang Cadangan', 'location' => 'Area penyimpanan tambahan', 'is_store' => false, 'is_active' => true]
            );
        }

        return [
            'store' => $store->fresh(),
            'main' => $mainWarehouse->fresh(),
            'backup' => $backupWarehouse->fresh(),
        ];
    }

    private function seedSuppliers(): array
    {
        $rows = json_decode($this->suppliersJson(), true, 512, JSON_THROW_ON_ERROR);
        $suppliers = [];
        foreach ($rows as $row) {
            $supplier = Supplier::create([
                'name' => $row['name'],
                'address' => $row['address'],
                'contact_person' => $row['contact_person'],
                'phone' => $row['phone'],
            ]);
            $suppliers[$supplier->name] = $supplier;
        }
        return $suppliers;
    }

    private function seedProducts(array $warehouses): array
    {
        $rows = json_decode($this->productsJson(), true, 512, JSON_THROW_ON_ERROR);
        $categorySeq = [];
        $products = [];

        foreach ($rows as $row) {
            $category = strtoupper(trim($row['category'] ?: 'LAIN-LAIN'));
            $categorySeq[$category] = ($categorySeq[$category] ?? 0) + 1;
            $code = $this->categoryPrefix($category) . '-' . str_pad((string) $categorySeq[$category], 3, '0', STR_PAD_LEFT);

            $rawStock = max(0, (int) round((float) $row['stock']));
            if ($rawStock <= 3) {
                $storeStock = $rawStock;
            } else {
                $storeStock = (int) floor($rawStock * 0.68);
            }
            $mainStock = max(0, $rawStock - $storeStock);

            $product = Product::create([
                'product_code' => $code,
                'product_name' => $row['product_name'],
                'category' => $category,
                'unit' => strtoupper($row['unit'] ?: 'PCS'),
                'selling_price' => max(0, (float) $row['selling_price']),
                'hpp' => max(0, (float) $row['hpp']),
                'stock' => $storeStock,
                'minimum_stock' => max(1, (int) $row['minimum_stock']),
                'is_active' => (bool) $row['is_active'],
            ]);

            foreach ($warehouses as $key => $warehouse) {
                WarehouseStock::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'stock' => $key === 'store' ? $storeStock : ($key === 'main' ? $mainStock : 0),
                    'minimum_stock' => max(1, (int) $row['minimum_stock']),
                ]);
            }

            $products[] = $product->fresh();
        }

        return $products;
    }

    private function seedCustomers(): array
    {
        $rows = json_decode($this->customersJson(), true, 512, JSON_THROW_ON_ERROR);
        $customers = [];
        foreach ($rows as $idx => $row) {
            $registeredAt = Carbon::parse($this->startDate)->subDays(12 + ($idx % 35))->toDateString();
            $customer = Customer::create([
                'full_name' => $row['full_name'],
                'whatsapp_number' => $row['whatsapp_number'],
                'address' => $row['address'],
                'tier' => 'bronze',
                'total_accumulation' => 0,
                'point_balance' => 0,
                'registered_at' => $registeredAt,
            ]);
            $customers[] = $customer;
        }
        return $customers;
    }

    private function seedPromotions(array $products, int $ownerId): void
    {
        $candidates = array_values(array_filter($products, fn ($p) => $p->selling_price >= 15000 && $p->stock > 20));
        $selected = $this->pickMany($candidates, 8);
        foreach ($selected as $i => $product) {
            $discount = $this->roundTo(max(1000, min(15000, (float) $product->selling_price * mt_rand(3, 8) / 100)), 500);
            Promotion::create([
                'promo_name' => 'Promo Musim Tanam ' . ($i + 1),
                'product_id' => $product->id,
                'discount_amount' => $discount,
                'start_date' => '2026-05-25',
                'end_date' => '2026-06-07',
                'is_active' => true,
                'notes' => 'Promo awal implementasi sistem untuk produk cepat bergerak.',
                'created_by' => $ownerId,
            ]);
        }
    }

    private function buildStockMap(): array
    {
        $stock = [];
        WarehouseStock::query()->get()->each(function ($row) use (&$stock) {
            $stock[$row->warehouse_id][$row->product_id] = (int) $row->stock;
        });
        return $stock;
    }

    private function seedPurchases(array $products, array $suppliers, array $warehouses, array $users, array &$stock): void
    {
        $byCategory = $this->groupProductsByCategory($products);
        $plans = [
            ['2026-05-25', 'OBAT', 6],
            ['2026-05-26', 'BNH', 5],
            ['2026-05-27', 'OLI', 5],
            ['2026-05-28', 'OBAT', 6],
            ['2026-05-29', 'SLG', 4],
            ['2026-05-30', 'BNH', 5],
            ['2026-05-31', 'OBAT', 6],
            ['2026-06-01', 'OLI', 5],
            ['2026-06-02', 'MULSA', 3],
            ['2026-06-02', 'TERPAL', 3],
            ['2026-06-03', 'OBAT', 6],
        ];

        $invoiceSeq = 1;
        foreach ($plans as [$date, $category, $itemCount]) {
            $warehouse = mt_rand(1, 100) <= 72 ? $warehouses['main'] : $warehouses['store'];
            $supplier = $this->supplierForCategory($category, $suppliers);
            $purchaseAt = Carbon::parse($date . ' ' . str_pad((string) mt_rand(8, 15), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) mt_rand(0, 55), 2, '0', STR_PAD_LEFT) . ':00');
            $purchase = Purchase::create([
                'invoice_number' => 'SJ-' . $purchaseAt->format('ymd') . '-' . str_pad((string) $invoiceSeq, 3, '0', STR_PAD_LEFT),
                'supplier_id' => $supplier->id,
                'user_id' => $users['admin']->id,
                'warehouse_id' => $warehouse->id,
                'purchase_date' => $purchaseAt->toDateString(),
                'total_price' => 0,
                'notes' => 'Restok barang dari supplier sesuai faktur/surat jalan.',
                'created_at' => $purchaseAt,
                'updated_at' => $purchaseAt,
            ]);
            $invoiceSeq++;

            $candidates = $byCategory[$category] ?? $products;
            usort($candidates, fn ($a, $b) => ((int) $a->stock <=> (int) $b->stock));
            $chosen = $this->pickMany($candidates, min($itemCount, count($candidates)));
            $total = 0;

            foreach ($chosen as $product) {
                $qty = $this->purchaseQtyFor($product);
                $unitBuyPrice = $this->roundTo((float) $product->hpp * mt_rand(97, 103) / 100, 100);
                if ($unitBuyPrice <= 0) {
                    $unitBuyPrice = $this->roundTo((float) $product->selling_price * 0.8, 100);
                }
                $subtotal = $qty * $unitBuyPrice;
                $total += $subtotal;

                $oldTotalStock = $this->totalStockForProduct($stock, $product->id);
                $oldHpp = (float) $product->hpp;
                $newHpp = $oldTotalStock + $qty > 0
                    ? round((($oldHpp * $oldTotalStock) + ($unitBuyPrice * $qty)) / ($oldTotalStock + $qty), 2)
                    : $unitBuyPrice;

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'unit_buy_price' => $unitBuyPrice,
                    'new_hpp' => $newHpp,
                    'subtotal' => $subtotal,
                    'created_at' => $purchaseAt,
                    'updated_at' => $purchaseAt,
                ]);

                $stock[$warehouse->id][$product->id] = ($stock[$warehouse->id][$product->id] ?? 0) + $qty;
                $product->forceFill(['hpp' => $newHpp])->save();
            }

            $purchase->forceFill(['total_price' => $total, 'updated_at' => $purchaseAt])->save();
            $this->insertLog($users['admin']->id, 'PURCHASE', 'Input pembelian ' . $purchase->invoice_number . ' dari ' . $supplier->name . ' — Total: Rp ' . number_format($total, 0, ',', '.'), $purchaseAt);
        }
    }

    private function seedTransfers(array $products, array $warehouses, array $users, array &$stock): void
    {
        $dates = ['2026-05-27', '2026-05-29', '2026-06-01', '2026-06-03'];
        foreach ($dates as $idx => $date) {
            $transferAt = Carbon::parse($date . ' ' . str_pad((string) mt_rand(10, 16), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) mt_rand(0, 55), 2, '0', STR_PAD_LEFT) . ':00');
            $transfer = StockTransfer::create([
                'transfer_number' => 'TRF-' . $transferAt->format('ymd') . '-' . str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT),
                'from_warehouse_id' => $warehouses['main']->id,
                'to_warehouse_id' => $warehouses['store']->id,
                'user_id' => $users['admin']->id,
                'status' => 'completed',
                'notes' => 'Pemindahan stok dari gudang ke toko untuk kebutuhan penjualan.',
                'transfer_date' => $transferAt->toDateString(),
                'created_at' => $transferAt,
                'updated_at' => $transferAt,
            ]);

            $candidates = array_values(array_filter($products, function ($product) use ($warehouses, $stock) {
                return ($stock[$warehouses['main']->id][$product->id] ?? 0) >= 5;
            }));

            foreach ($this->pickMany($candidates, min(4, count($candidates))) as $product) {
                $available = $stock[$warehouses['main']->id][$product->id] ?? 0;
                $qty = min($available, mt_rand(2, 12));
                if ($qty <= 0) {
                    continue;
                }

                StockTransferDetail::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'created_at' => $transferAt,
                    'updated_at' => $transferAt,
                ]);

                $stock[$warehouses['main']->id][$product->id] -= $qty;
                $stock[$warehouses['store']->id][$product->id] = ($stock[$warehouses['store']->id][$product->id] ?? 0) + $qty;
            }

            $this->insertLog($users['admin']->id, 'STOCK_TRANSFER', 'Transfer stok ' . $transfer->transfer_number . ' dari gudang ke toko.', $transferAt);
        }
    }

    private function seedTransactions(array $products, array $customers, array $warehouses, array $users, MembershipRule $rule, array &$stock): void
    {
        $promoByProduct = Promotion::query()->where('is_active', true)->get()->keyBy('product_id');
        $period = $this->dateRange($this->startDate, $this->endDate);
        $sequenceByDate = [];
        $storeId = $warehouses['store']->id;

        foreach ($period as $date) {
            $day = Carbon::parse($date);
            $count = $day->isSunday() ? mt_rand(7, 12) : mt_rand(9, 18);

            for ($i = 0; $i < $count; $i++) {
                $time = Carbon::parse($date . ' ' . str_pad((string) mt_rand(7, 19), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) mt_rand(0, 59), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) mt_rand(0, 59), 2, '0', STR_PAD_LEFT));
                $sequenceByDate[$date] = ($sequenceByDate[$date] ?? 0) + 1;
                $customer = mt_rand(1, 100) <= 73 ? $customers[array_rand($customers)]->fresh() : null;
                $details = [];
                $itemTarget = mt_rand(1, 100) <= 55 ? 1 : (mt_rand(1, 100) <= 82 ? 2 : mt_rand(3, 4));

                $tries = 0;
                while (count($details) < $itemTarget && $tries < 50) {
                    $tries++;
                    $product = $this->pickSellableProduct($products, $stock, $storeId);
                    if (! $product) {
                        break;
                    }

                    if (isset($details[$product->id])) {
                        continue;
                    }

                    $available = $stock[$storeId][$product->id] ?? 0;
                    $qty = min($available, $this->salesQtyFor($product));
                    if ($qty <= 0) {
                        continue;
                    }

                    $unitPrice = (float) $product->selling_price;
                    $promoDiscount = 0;
                    if ($promoByProduct->has($product->id) && mt_rand(1, 100) <= 50) {
                        $promoDiscount = min((float) $promoByProduct[$product->id]->discount_amount, $unitPrice * 0.2);
                    }
                    $finalUnitPrice = max(0, $unitPrice - $promoDiscount);
                    $lineSubtotal = $finalUnitPrice * $qty;

                    $details[$product->id] = [
                        'product' => $product,
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'final_unit_price' => $finalUnitPrice,
                        'subtotal' => $lineSubtotal,
                    ];
                }

                if (count($details) === 0) {
                    continue;
                }

                $subtotal = array_sum(array_column($details, 'subtotal'));
                $discountPercent = $customer ? (float) $rule->getDiscountForTier($customer->tier) : 0;
                $discountAmount = round($subtotal * $discountPercent / 100, 2);
                $afterMemberDiscount = max(0, $subtotal - $discountAmount);

                $pointsRedeemed = 0;
                $pointRedeemAmount = 0;
                if ($customer && $day->gte(Carbon::parse('2026-05-29')) && mt_rand(1, 100) <= 18) {
                    $maxPointValue = $afterMemberDiscount * ((float) $rule->max_redeem_percent / 100);
                    $maxPoints = (int) floor($maxPointValue / max(1, (float) $rule->redeem_point_value));
                    $availablePoints = (int) floor((float) $customer->point_balance);
                    $usable = min($maxPoints, $availablePoints);
                    if ($usable >= (int) $rule->minimum_redeem_points) {
                        $pointsRedeemed = max((int) $rule->minimum_redeem_points, (int) floor(mt_rand((int) $rule->minimum_redeem_points, $usable) / 10) * 10);
                        $pointRedeemAmount = $pointsRedeemed * (float) $rule->redeem_point_value;
                    }
                }

                $total = max(0, $afterMemberDiscount - $pointRedeemAmount);
                $cashReceived = $this->cashReceivedFor($total);
                $change = max(0, $cashReceived - $total);
                $pointsEarned = $customer ? floor($total / max(1, (int) $rule->point_per_nominal)) : 0;

                $trx = Transaction::create([
                    'transaction_number' => 'TRX-' . $day->format('Ymd') . '-' . str_pad((string) $sequenceByDate[$date], 4, '0', STR_PAD_LEFT),
                    'cashier_id' => $users['cashier']->id,
                    'customer_id' => $customer?->id,
                    'subtotal' => $subtotal,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'total_price' => $total,
                    'cash_received' => $cashReceived,
                    'change_amount' => $change,
                    'points_earned' => $pointsEarned,
                    'points_redeemed' => $pointsRedeemed,
                    'point_redeem_amount' => $pointRedeemAmount,
                    'payment_status' => 'paid',
                    'notes' => $this->transactionNote(),
                    'transaction_date' => $time,
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);

                foreach ($details as $line) {
                    TransactionDetail::create([
                        'transaction_id' => $trx->id,
                        'product_id' => $line['product']->id,
                        'qty' => $line['qty'],
                        'unit_price' => $line['unit_price'],
                        'final_unit_price' => $line['final_unit_price'],
                        'subtotal' => $line['subtotal'],
                        'created_at' => $time,
                        'updated_at' => $time,
                    ]);
                    $stock[$storeId][$line['product']->id] -= $line['qty'];
                }

                if ($customer) {
                    if ($pointsRedeemed > 0) {
                        PointHistory::create([
                            'customer_id' => $customer->id,
                            'transaction_id' => $trx->id,
                            'points_earned' => -$pointsRedeemed,
                            'description' => 'Redeem poin untuk transaksi ' . $trx->transaction_number,
                            'created_at' => $time,
                            'updated_at' => $time,
                        ]);
                    }
                    if ($pointsEarned > 0) {
                        PointHistory::create([
                            'customer_id' => $customer->id,
                            'transaction_id' => $trx->id,
                            'points_earned' => $pointsEarned,
                            'description' => 'Poin dari transaksi ' . $trx->transaction_number,
                            'created_at' => $time,
                            'updated_at' => $time,
                        ]);
                    }

                    $newAccumulation = (float) $customer->total_accumulation + $total;
                    $newPointBalance = max(0, (float) $customer->point_balance - $pointsRedeemed + $pointsEarned);
                    $customer->forceFill([
                        'total_accumulation' => $newAccumulation,
                        'point_balance' => $newPointBalance,
                        'tier' => $this->tierForAccumulation($newAccumulation, $rule),
                        'updated_at' => $time,
                    ])->save();
                    $customer = $customer->fresh();
                }

                $this->insertLog($users['cashier']->id, 'TRANSACTION', 'Transaksi ' . $trx->transaction_number . ' — Total: Rp ' . number_format($total, 0, ',', '.'), $time);
            }
        }
    }

    private function seedStockOpname(array $products, array $warehouses, array $users, array &$stock): void
    {
        $dates = ['2026-05-26', '2026-05-28', '2026-05-30', '2026-06-01', '2026-06-02', '2026-06-03'];
        $warehouseList = [$warehouses['store'], $warehouses['main']];
        foreach ($dates as $idx => $date) {
            $warehouse = $warehouseList[$idx % count($warehouseList)];
            $candidates = array_values(array_filter($products, fn ($p) => ($stock[$warehouse->id][$p->id] ?? 0) > 3));
            if (! $candidates) {
                continue;
            }
            $product = $candidates[array_rand($candidates)];
            $before = $stock[$warehouse->id][$product->id] ?? 0;
            $difference = mt_rand(-2, 3);
            if ($before + $difference < 0) {
                $difference = 0;
            }
            $after = $before + $difference;
            $stock[$warehouse->id][$product->id] = $after;
            $time = Carbon::parse($date . ' ' . str_pad((string) mt_rand(15, 18), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) mt_rand(0, 55), 2, '0', STR_PAD_LEFT) . ':00');

            StockAdjustment::create([
                'product_id' => $product->id,
                'user_id' => $users['admin']->id,
                'warehouse_id' => $warehouse->id,
                'stock_before' => $before,
                'stock_after' => $after,
                'difference' => $difference,
                'notes' => 'Stock opname rutin ' . $warehouse->name . ' setelah pengecekan fisik.',
                'adjustment_date' => $time->toDateString(),
                'created_at' => $time,
                'updated_at' => $time,
            ]);
            $this->insertLog($users['admin']->id, 'STOCK_OPNAME', 'Stock opname ' . $product->product_name . ' di ' . $warehouse->name . ' (' . ($difference >= 0 ? '+' : '') . $difference . ').', $time);
        }
    }

    private function syncFinalProductAndWarehouseStocks(array $products, array $stock, array $warehouses): void
    {
        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                WarehouseStock::updateOrCreate(
                    ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
                    ['stock' => max(0, (int) ($stock[$warehouse->id][$product->id] ?? 0)), 'minimum_stock' => (int) $product->minimum_stock]
                );
            }

            $storeStock = max(0, (int) ($stock[$warehouses['store']->id][$product->id] ?? 0));
            Product::whereKey($product->id)->update(['stock' => $storeStock]);
        }
    }

    private function insertLog(?int $userId, string $action, string $detail, Carbon $time): void
    {
        DB::table('activity_logs')->insert([
            'user_id' => $userId,
            'action' => $action,
            'detail' => $detail,
            'ip_address' => '127.0.0.1',
            'created_at' => $time,
            'updated_at' => $time,
        ]);
    }

    private function categoryPrefix(string $category): string
    {
        return match (strtoupper($category)) {
            'BNH' => 'BNH',
            'OBAT' => 'OBT',
            'OLI' => 'OLI',
            'SLG' => 'SLG',
            'MULSA' => 'MLS',
            'ROLL' => 'ROL',
            'TERPAL' => 'TRP',
            default => 'LLN',
        };
    }

    private function groupProductsByCategory(array $products): array
    {
        $grouped = [];
        foreach ($products as $product) {
            $grouped[strtoupper($product->category ?: 'LAIN-LAIN')][] = $product;
        }
        return $grouped;
    }

    private function supplierForCategory(string $category, array $suppliers): Supplier
    {
        $names = match (strtoupper($category)) {
            'BNH' => ['Oriental Seed', 'NTM Seed', 'Pasti Hasil', 'Payung Agung'],
            'OBAT' => ['Petrosida Gresik', 'Petrokimia Kayaku', 'Mitra Agro Lestari', 'Mitra Agro Sukses', 'PT Agro Cemerlang Plasindo'],
            'OLI' => ['Pinarak Diesel', 'PT Mega Jaya Net', 'PT Sumber Baru Ban'],
            'SLG', 'MULSA', 'TERPAL' => ['MEY Paranet Bandung', 'Mulsa Prima Ronanda', 'Plastik 100 Solo'],
            'ROLL' => ['Prima Karya', 'PT Mega Jaya Net'],
            default => array_keys($suppliers),
        };

        $name = $names[array_rand($names)];
        return $suppliers[$name] ?? reset($suppliers);
    }

    private function pickMany(array $items, int $count): array
    {
        if ($count <= 0 || count($items) === 0) {
            return [];
        }
        shuffle($items);
        return array_slice($items, 0, min($count, count($items)));
    }

    private function totalStockForProduct(array $stock, int $productId): int
    {
        $total = 0;
        foreach ($stock as $warehouseStock) {
            $total += (int) ($warehouseStock[$productId] ?? 0);
        }
        return $total;
    }

    private function purchaseQtyFor(Product $product): int
    {
        $price = (float) $product->hpp;
        if ($price >= 200000) {
            return mt_rand(1, 4);
        }
        if ($price >= 70000) {
            return mt_rand(2, 8);
        }
        if ($price >= 20000) {
            return mt_rand(4, 18);
        }
        return mt_rand(12, 45);
    }

    private function salesQtyFor(Product $product): int
    {
        $price = (float) $product->selling_price;
        $unit = strtoupper((string) $product->unit);
        if ($price <= 10000 || in_array($unit, ['SCHT', 'SACHET'], true)) {
            return mt_rand(1, 12);
        }
        if ($price <= 35000) {
            return mt_rand(1, 5);
        }
        if ($price >= 150000) {
            return mt_rand(1, 2);
        }
        return mt_rand(1, 4);
    }

    private function pickSellableProduct(array $products, array $stock, int $storeId): ?Product
    {
        $categoryWeights = [
            'OBAT' => 42,
            'BNH' => 24,
            'OLI' => 14,
            'SLG' => 8,
            'MULSA' => 4,
            'TERPAL' => 4,
            'ROLL' => 2,
            'LAIN-LAIN' => 2,
        ];
        $category = $this->weightedPick($categoryWeights);
        $filtered = array_values(array_filter($products, function ($product) use ($stock, $storeId, $category) {
            return strtoupper((string) $product->category) === $category
                && (float) $product->selling_price > 0
                && ($stock[$storeId][$product->id] ?? 0) > 0;
        }));

        if (! $filtered) {
            $filtered = array_values(array_filter($products, fn ($product) => (float) $product->selling_price > 0 && ($stock[$storeId][$product->id] ?? 0) > 0));
        }

        return $filtered ? $filtered[array_rand($filtered)] : null;
    }

    private function weightedPick(array $weights): string
    {
        $total = array_sum($weights);
        $rand = mt_rand(1, $total);
        $running = 0;
        foreach ($weights as $key => $weight) {
            $running += $weight;
            if ($rand <= $running) {
                return $key;
            }
        }
        return array_key_first($weights);
    }

    private function cashReceivedFor(float $total): float
    {
        if ($total <= 0) {
            return 0;
        }
        if (mt_rand(1, 100) <= 38) {
            return $total;
        }
        $rounding = $total < 100000 ? 10000 : 50000;
        return ceil($total / $rounding) * $rounding;
    }

    private function tierForAccumulation(float $accumulation, MembershipRule $rule): string
    {
        if ($accumulation >= (float) $rule->tier_gold_min) {
            return 'gold';
        }
        if ($accumulation >= (float) $rule->tier_silver_min) {
            return 'silver';
        }
        return 'bronze';
    }

    private function transactionNote(): string
    {
        $notes = [
            'Pembelian langsung di toko.',
            'Pelanggan membeli kebutuhan musim tanam.',
            'Transaksi kasir normal.',
            'Belanja tambahan untuk stok lahan.',
            'Pelanggan langganan toko.',
            'Pembelian cepat tanpa catatan khusus.',
        ];
        return $notes[array_rand($notes)];
    }

    private function dateRange(string $start, string $end): array
    {
        $dates = [];
        $cursor = Carbon::parse($start)->startOfDay();
        $last = Carbon::parse($end)->startOfDay();
        while ($cursor->lte($last)) {
            $dates[] = $cursor->toDateString();
            $cursor->addDay();
        }
        return $dates;
    }

    private function roundTo(float $value, int $nearest): float
    {
        return round($value / $nearest) * $nearest;
    }

    private function productsJson(): string
    {
        return <<<'JSON'
[
  {
    "product_name": "Kc Parade 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 58000,
    "hpp": 46400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kc SETUJU 100 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 20000,
    "hpp": 16000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kp Ecer",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 14500,
    "hpp": 11600,
    "stock": 118,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kp Persada BISI 50 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 14000,
    "hpp": 11200,
    "stock": 30,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kp WEILING SUPER 100gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 8,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kp WINA 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 78000,
    "hpp": 62400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kp Pertiwi 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 80000,
    "hpp": 64000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Lado Ecer",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 8000,
    "hpp": 6400,
    "stock": 11,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mekongga 99 Premium/5kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 90000,
    "hpp": 72000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Melon ACTION 550 Seeds",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 125000,
    "hpp": 100000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Melon ARAMIS",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 100000,
    "hpp": 80000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Melon Attack",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 50000,
    "hpp": 40000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Melon Ivory 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 100000,
    "hpp": 80000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Melon Japonica",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 100000,
    "hpp": 80000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Melon Leader 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 105000,
    "hpp": 84000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Melon ANVI Pertiwi",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 183000,
    "hpp": 146400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Melon Sakata Glamour/ 100 Seeds",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 54000,
    "hpp": 43200,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Padi Memberamo PPKJ/5 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 87000,
    "hpp": 69600,
    "stock": 6,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Oyong OR Bintang Hijau 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 19000,
    "hpp": 15200,
    "stock": 6,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Oyong Ladies 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 85,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Oyong Ladies 50 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 84000,
    "hpp": 67200,
    "stock": 10,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Oyong Ecer",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 4000,
    "hpp": 3200,
    "stock": 72,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Padi Padjajaran JUMBO/5 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 80000,
    "hpp": 64000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Padi PAK TIWI-2 / 5 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 100000,
    "hpp": 80000,
    "stock": 35,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pare Ecer",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 4500,
    "hpp": 3600,
    "stock": 67,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pare Asoka 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 84,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pare Sembada 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 22000,
    "hpp": 17600,
    "stock": 2,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pare Raden 50s",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 44000,
    "hpp": 35200,
    "stock": 60,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pare Sriwedari 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 22000,
    "hpp": 17600,
    "stock": 2,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pare TRINITY 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 27000,
    "hpp": 21600,
    "stock": 63,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pare TRINITY 50 gr",
    "category": "BNH",
    "unit": "KLG",
    "selling_price": 88000,
    "hpp": 70400,
    "stock": 10,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pare WELUT WIRA 02",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 23,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pare PANTURA 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 28000,
    "hpp": 22400,
    "stock": 6,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pepaya California Benih Inti",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pepaya NTM Seed",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 23000,
    "hpp": 18400,
    "stock": 9,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pepaya Bangkok 5 gram",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 15000,
    "hpp": 12000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Sawi Mandala 20 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 14000,
    "hpp": 11200,
    "stock": 7,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Sawi SHINTA 25 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 17000,
    "hpp": 13600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Selada GRAND RAPIDS",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 28000,
    "hpp": 22400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Seledri Ta Fung",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 15000,
    "hpp": 12000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Semangka OR46",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 39000,
    "hpp": 31200,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Semangka Bali Flower 20 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 72000,
    "hpp": 57600,
    "stock": 45,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Semangka OR ORION Kuning",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 65000,
    "hpp": 52000,
    "stock": 6,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Semangka Red New Dragon 20 gram",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 65000,
    "hpp": 52000,
    "stock": 16,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Semangka OR Mini Dragon",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Padi Sunggal HT Merah /5 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 83000,
    "hpp": 66400,
    "stock": 18,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Padi M70 /5 Kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 100000,
    "hpp": 80000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Sawi Tosakan 100 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 55000,
    "hpp": 44000,
    "stock": 27,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Sawi Tosakan 25 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 17000,
    "hpp": 13600,
    "stock": 417,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Terong Bimbi 5 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 7,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Terong OR Fabian 5gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 20000,
    "hpp": 16000,
    "stock": 7,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Terong OR Gading 5gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 20000,
    "hpp": 16000,
    "stock": 11,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung Manis OR Holili 250 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 40000,
    "hpp": 32000,
    "stock": 12,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung NK ANDALAN 007 1 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 77000,
    "hpp": 61600,
    "stock": 7,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung NK 212 SAKTI PENDEKAR",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 109000,
    "hpp": 87200,
    "stock": 83,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung NK WIRO 212 1 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 82000,
    "hpp": 65600,
    "stock": 117,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung NK PERKASA 6172 /1 Kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 125000,
    "hpp": 100000,
    "stock": 62,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung NK 6172 SAKTI PERKASA",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 155000,
    "hpp": 124000,
    "stock": 38,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung NK SUMO 7328 / 1 Kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 125000,
    "hpp": 100000,
    "stock": 204,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung NK 7328 SAKTI SUMO/ 1 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 145000,
    "hpp": 116000,
    "stock": 71,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung P27/1kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 103000,
    "hpp": 82400,
    "stock": 13,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung P27/1kg LUMIGEN",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 120000,
    "hpp": 96000,
    "stock": 18,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung Pertiwi 3",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 69000,
    "hpp": 55200,
    "stock": 23,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung Pertiwi 6",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 92000,
    "hpp": 73600,
    "stock": 38,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kangkung Ecer 250 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 14000,
    "hpp": 11200,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kangkung Ecer",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 4500,
    "hpp": 3600,
    "stock": 61,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kangkung Serimpi 1 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 70000,
    "hpp": 56000,
    "stock": 105,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kangkung TF BL 500 gram",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 30000,
    "hpp": 24000,
    "stock": 62,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kangkung TF Super 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 20000,
    "hpp": 16000,
    "stock": 110,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kangkung Tomado 500gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 96,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kp BORNEO 100 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 109,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kp BORNEO 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 140000,
    "hpp": 112000,
    "stock": 4,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kc Elang Putih 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 45000,
    "hpp": 36000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kp OR SHINE 250 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 40000,
    "hpp": 32000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kp Hj Star HP/MP 100gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kp Hj Star HP 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 80000,
    "hpp": 64000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kp Hj Star MP 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 80000,
    "hpp": 64000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kcp Mutiara 100 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 15000,
    "hpp": 12000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kc Pangeran 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 50000,
    "hpp": 40000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe SERAMBI 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 90000,
    "hpp": 72000,
    "stock": 13,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe OR Trisula Putih 10gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 22000,
    "hpp": 17600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe OR Twist 22",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 110000,
    "hpp": 88000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe OR Twist 33",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 100000,
    "hpp": 80000,
    "stock": 1,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe OR Twist 42",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 180000,
    "hpp": 144000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe OR Twist Satria 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 110000,
    "hpp": 88000,
    "stock": 11,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Caisim 25 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 10000,
    "hpp": 8000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Padi Cakra Buana JUMBO/5kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 80000,
    "hpp": 64000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cibogo HT/5 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 55000,
    "hpp": 44000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Padi Ciherang PPKJ /5 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 87000,
    "hpp": 69600,
    "stock": 5,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Oyong Ganesha 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 32,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Padi Inpari 32 GAJAH /5 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 100000,
    "hpp": 80000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Padi Inpari 32 PPKJ /5 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 87000,
    "hpp": 69600,
    "stock": 93,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Padi Inpari 32 JUMBO/5 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 82000,
    "hpp": 65600,
    "stock": 138,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Padi Inpari 32 PERTIWI/5kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 95000,
    "hpp": 76000,
    "stock": 37,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Padi Inpari 32 PPKJ Premium/ 5 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 90000,
    "hpp": 72000,
    "stock": 68,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "MANTAP Gajah 5 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 98000,
    "hpp": 78400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung Bisi 18 NEW /1kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 105000,
    "hpp": 84000,
    "stock": 119,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung Bisi 2/1 kg",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 75000,
    "hpp": 60000,
    "stock": 70,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung ECER 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 48000,
    "hpp": 38400,
    "stock": 7,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung Bisi 234 MASKOT",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 89000,
    "hpp": 71200,
    "stock": 20,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung ECER 250 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 20,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung Manis Favorit 100 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 24000,
    "hpp": 19200,
    "stock": 87,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung Manis Favorit 250 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 277,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung MANIS ecer",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 7500,
    "hpp": 6000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jagung Manis OR Holili 200 Bj",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 17000,
    "hpp": 13600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bayam Ayuna 100 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 16000,
    "hpp": 12800,
    "stock": 14,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bayam Ecer NEW",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 5500,
    "hpp": 4400,
    "stock": 28,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bayam Retina 100 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 17500,
    "hpp": 14000,
    "stock": 90,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bayam Taifung 500 gram",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 44000,
    "hpp": 35200,
    "stock": 137,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Blewah BALADEWA 20 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 19000,
    "hpp": 15200,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Blewah Bima 15 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 18000,
    "hpp": 14400,
    "stock": 12,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Blewah Ecer",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 3500,
    "hpp": 2800,
    "stock": 19,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Buncis Ecer",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 7500,
    "hpp": 6000,
    "stock": 11,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Buncis Gravo 500gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 56000,
    "hpp": 44800,
    "stock": 38,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Buncis LEBAT 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 49000,
    "hpp": 39200,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Buncis Mepsina 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 53000,
    "hpp": 42400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Buncis TW 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 55000,
    "hpp": 44000,
    "stock": 8,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Buncis Berlian 500 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 48000,
    "hpp": 38400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe BAJA F1 1500s",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 225000,
    "hpp": 180000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe BAJA MC 1500s",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 225000,
    "hpp": 180000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe Ecer",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 4500,
    "hpp": 3600,
    "stock": 34,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe OR Dana 10 gram",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 95000,
    "hpp": 76000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe OR DJITU 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 150000,
    "hpp": 120000,
    "stock": 1,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe OR Kencana 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe RED KING 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 140000,
    "hpp": 112000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe OR Beautifull",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 140000,
    "hpp": 112000,
    "stock": 5,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe OR Cempluk 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 45000,
    "hpp": 36000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe PERTIWI 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 50000,
    "hpp": 40000,
    "stock": 13,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe ROMARIO 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 30000,
    "hpp": 24000,
    "stock": 6,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe SAMPLE",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 3,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe SEKAR 10 gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 39000,
    "hpp": 31200,
    "stock": 44,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cabe OR Trisula Hijau 10gr",
    "category": "BNH",
    "unit": "PCS",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 12,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mulsa Action Roll B/T",
    "category": "MULSA",
    "unit": "KILO",
    "selling_price": 45000,
    "hpp": 36000,
    "stock": 80,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mulsa HP Hercules 1 kg",
    "category": "MULSA",
    "unit": "PCS",
    "selling_price": 49000,
    "hpp": 39200,
    "stock": 191,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mulsa Prima Roll B/T/K",
    "category": "MULSA",
    "unit": "KILO",
    "selling_price": 54000,
    "hpp": 43200,
    "stock": 91,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gibgrow PANEN",
    "category": "OBAT",
    "unit": "SCHT",
    "selling_price": 7500,
    "hpp": 6000,
    "stock": 1931,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gibgrow SP 1gr",
    "category": "OBAT",
    "unit": "SCHT",
    "selling_price": 4500,
    "hpp": 3600,
    "stock": 3358,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gudik 88 Spray",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 15000,
    "hpp": 12000,
    "stock": 1,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "ROGER 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 57000,
    "hpp": 45600,
    "stock": 75,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gempur 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 62000,
    "hpp": 49600,
    "stock": 224,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Asam Amino 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 40000,
    "hpp": 32000,
    "stock": 17,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Zinatra 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 87000,
    "hpp": 69600,
    "stock": 47,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gibgrow TB 5gr",
    "category": "OBAT",
    "unit": "SCHT",
    "selling_price": 26000,
    "hpp": 20800,
    "stock": 362,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gisentro 500 ml",
    "category": "OBAT",
    "unit": "PAKET",
    "selling_price": 119000,
    "hpp": 95200,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "BMA 1 LT",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 99000,
    "hpp": 79200,
    "stock": 22,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Glido 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 105000,
    "hpp": 84000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Glido 500 ml + Kaos",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 170000,
    "hpp": 136000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "BARCA 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 72000,
    "hpp": 57600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Lamdarin 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 42000,
    "hpp": 33600,
    "stock": 27,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Goal 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 59000,
    "hpp": 47200,
    "stock": 7,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Hoky Stik 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 14000,
    "hpp": 11200,
    "stock": 40,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gramoxone 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 66000,
    "hpp": 52800,
    "stock": 307,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gramoxone 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 24000,
    "hpp": 19200,
    "stock": 195,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gramoxone 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 39000,
    "hpp": 31200,
    "stock": 247,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Growth Cell 1 kg",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 21000,
    "hpp": 16800,
    "stock": 53,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "AMUFOS 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 65000,
    "hpp": 52000,
    "stock": 82,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "GAUCHO 20 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gayemi",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 3500,
    "hpp": 2800,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Hajar 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 75000,
    "hpp": 60000,
    "stock": 31,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Hajar 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 180000,
    "hpp": 144000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Hajar 50 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 41000,
    "hpp": 32800,
    "stock": 20,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Shining Star 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 135000,
    "hpp": 108000,
    "stock": 5,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "HELUX 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 15000,
    "hpp": 12000,
    "stock": 2,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Folicur Gold 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 75000,
    "hpp": 60000,
    "stock": 14,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Folicur Gold 240 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 180000,
    "hpp": 144000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Folirfos 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 37000,
    "hpp": 29500,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Folium 1 kg",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 57000,
    "hpp": 45500,
    "stock": 15,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Folium 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Forsil 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 65000,
    "hpp": 52000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fortuner 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 20000,
    "hpp": 16000,
    "stock": 312,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fortuner 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 12000,
    "hpp": 9500,
    "stock": 239,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fostin 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 21000,
    "hpp": 17000,
    "stock": 231,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fostin 400 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 70000,
    "hpp": 56000,
    "stock": 206,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fetal 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 27000,
    "hpp": 21500,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fujiwan 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 22000,
    "hpp": 17500,
    "stock": 25,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fujiwan 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 84000,
    "hpp": 67000,
    "stock": 41,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Furio 2 kg",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 18500,
    "hpp": 15000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Furadan 1 kg",
    "category": "OBAT",
    "unit": "KILO",
    "selling_price": 22500,
    "hpp": 18000,
    "stock": 154,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Furadan 2 kg",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 38000,
    "hpp": 30500,
    "stock": 141,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "GA Bunga Buah 500ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 27000,
    "hpp": 21500,
    "stock": 39,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "GA Jagung 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 27000,
    "hpp": 21500,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gandasil Buah 100 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 13500,
    "hpp": 11000,
    "stock": 2925,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gandasil Buah 500 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 52000,
    "hpp": 41500,
    "stock": 524,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gandasil Daun 100 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 12500,
    "hpp": 10000,
    "stock": 1663,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Gandasil Daun 500 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 47000,
    "hpp": 37500,
    "stock": 423,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Elang Gold 5 lt",
    "category": "OBAT",
    "unit": "GLN",
    "selling_price": 270000,
    "hpp": 216000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "EM-4 Tani 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 28000,
    "hpp": 22400,
    "stock": 63,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "EM-4 Toilet 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 28000,
    "hpp": 22400,
    "stock": 9,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "EM-4 Ternak 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 28000,
    "hpp": 22400,
    "stock": 30,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Emcindo 400 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Envoy 100 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 50000,
    "hpp": 40000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Equation 100 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 80000,
    "hpp": 64000,
    "stock": 15,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Eros Gold 100 gr",
    "category": "OBAT",
    "unit": "SCHT",
    "selling_price": 20000,
    "hpp": 16000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Eros Gold 250 gr",
    "category": "OBAT",
    "unit": "SCHT",
    "selling_price": 45000,
    "hpp": 36000,
    "stock": 639,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Etrek 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 41,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Etrek 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 15000,
    "hpp": 12000,
    "stock": 4,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Explore NEW 250 ml + KAOS",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 110000,
    "hpp": 88000,
    "stock": 44,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Explore 80 ml + Tas",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 40000,
    "hpp": 32000,
    "stock": 30,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "PARANOX 1 LT",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 45000,
    "hpp": 36000,
    "stock": 19,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fastac 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 18000,
    "hpp": 14400,
    "stock": 66,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fenval 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 17000,
    "hpp": 13600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fenval 300 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 36000,
    "hpp": 28800,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fenval 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 59000,
    "hpp": 47200,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Ferterra 4GR 2kg",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 90000,
    "hpp": 72000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fertila Padi 1/500 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 27,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fertila Padi 2/500 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 46,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Filia 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 139000,
    "hpp": 111200,
    "stock": 78,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Filia 50 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 34000,
    "hpp": 27200,
    "stock": 270,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Maceno 80 gr",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 24000,
    "hpp": 19200,
    "stock": 271,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Folicur WP 50 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 30000,
    "hpp": 24000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Fokker 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 13000,
    "hpp": 10400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Demolish 50 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 45000,
    "hpp": 36000,
    "stock": 61,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Desanto 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 55000,
    "hpp": 44000,
    "stock": 34,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Desanto 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 125000,
    "hpp": 100000,
    "stock": 20,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "DESMOS 500 ml",
    "category": "OBAT",
    "unit": "PAKET",
    "selling_price": 125000,
    "hpp": 100000,
    "stock": 4,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Detazeb 1 kg",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 95000,
    "hpp": 76000,
    "stock": 8,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dharmasan 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 27000,
    "hpp": 21600,
    "stock": 204,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dharmasan 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 120000,
    "hpp": 96000,
    "stock": 33,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Diazinon 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 30000,
    "hpp": 24000,
    "stock": 26,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Diazinon 1 kg",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 47000,
    "hpp": 37600,
    "stock": 42,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dimec 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 90000,
    "hpp": 72000,
    "stock": 1,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dithane 1 kg",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 155000,
    "hpp": 124000,
    "stock": 22,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dithane 200 gr",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 40000,
    "hpp": 32000,
    "stock": 54,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dithane 500 gr",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 85000,
    "hpp": 68000,
    "stock": 26,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "DMA-6 NEW 200 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "DMA-6 400 ML",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 115000,
    "hpp": 92000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Denise 200 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 34000,
    "hpp": 27200,
    "stock": 41,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Ambition 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 95000,
    "hpp": 76000,
    "stock": 32,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "DAFFER",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 1500,
    "hpp": 1200,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dokoh",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 6500,
    "hpp": 5200,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dolgy 5 gr",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 4000,
    "hpp": 3200,
    "stock": 62,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dolomit 50 Kg",
    "category": "OBAT",
    "unit": "ZAK",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Donkey 100 gram",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Trisula 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 55000,
    "hpp": 44000,
    "stock": 18,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Asam Humad 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 34,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Elazaro Sachet 10 gram",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 3,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Elazaro 50 gram",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 140000,
    "hpp": 112000,
    "stock": 167,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "EM-4 Tambak 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 28000,
    "hpp": 22400,
    "stock": 28,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Counter 250 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 52000,
    "hpp": 41600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Prima Bomb 400 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 2,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Lamdarin 80 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 21000,
    "hpp": 16800,
    "stock": 80,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cruiser 12.5 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 30000,
    "hpp": 24000,
    "stock": 59,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Curacron 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 40000,
    "hpp": 32000,
    "stock": 25,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Curacron 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 142000,
    "hpp": 113600,
    "stock": 17,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Curacron 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 78000,
    "hpp": 62400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Daconil 500 gr",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 98000,
    "hpp": 78400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "DAFAT 100 gr",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 27000,
    "hpp": 21600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dafat 15 gr",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 5000,
    "hpp": 4000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dangke 100 gr NEW",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 340,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dangke 250 gr NEW",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 57000,
    "hpp": 45600,
    "stock": 298,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "De Besttan 100 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 65000,
    "hpp": 52000,
    "stock": 527,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "De Besttan 15 gr",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 15000,
    "hpp": 12000,
    "stock": 878,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Decis 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 31000,
    "hpp": 24800,
    "stock": 91,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Decis 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 74000,
    "hpp": 59200,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Decis 50 ml NEW",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 18500,
    "hpp": 14800,
    "stock": 197,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "DECOPRIMA",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 45000,
    "hpp": 36000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dekamon 1 kg",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dekamon 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 24000,
    "hpp": 19200,
    "stock": 55,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Dekamon 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 76000,
    "hpp": 60800,
    "stock": 23,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Delsene 100 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 22000,
    "hpp": 17600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Delsene 500 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 75000,
    "hpp": 60000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Demacide 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 51000,
    "hpp": 40800,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Demolish 100 ml + Kaos",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 75000,
    "hpp": 60000,
    "stock": 12,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Demolish 200 ml + Kaos",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 130000,
    "hpp": 104000,
    "stock": 7,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Centadine 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 36000,
    "hpp": 28800,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Centallyplus 5 gr",
    "category": "OBAT",
    "unit": "SCHT",
    "selling_price": 5000,
    "hpp": 4000,
    "stock": 8,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "KCC Kedelai Kacang 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 22,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Cepha 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 27000,
    "hpp": 21600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "ALTONIK 50 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 15000,
    "hpp": 12000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Clincher 200 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 77000,
    "hpp": 61600,
    "stock": 43,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Clipper 250 ml NEW",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 120000,
    "hpp": 96000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "CLOSER 7,5 gr",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 24000,
    "hpp": 19200,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "CNG 1 kg",
    "category": "OBAT",
    "unit": "KILO",
    "selling_price": 20000,
    "hpp": 16000,
    "stock": 10,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Columbus 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 73,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Columbus 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 55000,
    "hpp": 44000,
    "stock": 24,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Columbus 400 ml NEW",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 82000,
    "hpp": 65600,
    "stock": 15,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Matarin 80 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 19000,
    "hpp": 15200,
    "stock": 24,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "DELADAXIN 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 400000,
    "hpp": 320000,
    "stock": 3,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Ranger 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 37000,
    "hpp": 29600,
    "stock": 72,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Lamdarin 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 75000,
    "hpp": 60000,
    "stock": 36,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Confidor SL 60 ml",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 52000,
    "hpp": 41600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Confix 250 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 40000,
    "hpp": 32000,
    "stock": 3,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Confidor WP 100 gram",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Consento 60 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 30000,
    "hpp": 24000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "CONVEY 20 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 199000,
    "hpp": 159200,
    "stock": 7,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "CONVEY 40 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 330000,
    "hpp": 264000,
    "stock": 5,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Corona 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 85000,
    "hpp": 68000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Corona 250 ml + Kaos",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 185000,
    "hpp": 148000,
    "stock": 15,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Counter 100 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Prima Bomb 200 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 32000,
    "hpp": 25600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "BIO NPK 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 18000,
    "hpp": 14400,
    "stock": 17,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "BIO NPK 5 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 242,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Biotek 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 21000,
    "hpp": 16800,
    "stock": 15,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Biotek 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 13000,
    "hpp": 10400,
    "stock": 21,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Biotogrow 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 105000,
    "hpp": 84000,
    "stock": 3,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Nicofuron 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 120000,
    "hpp": 96000,
    "stock": 12,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "GDM Black Boss 1 kg",
    "category": "OBAT",
    "unit": "KILO",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 2,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Benfuron 25 gr",
    "category": "OBAT",
    "unit": "SCHT",
    "selling_price": 19000,
    "hpp": 15200,
    "stock": 751,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Boom Flower 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 40000,
    "hpp": 32000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Boom Flower 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 79000,
    "hpp": 63200,
    "stock": 2,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Boom Padi",
    "category": "OBAT",
    "unit": "PAKET",
    "selling_price": 86000,
    "hpp": 68800,
    "stock": 99,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Borer 5 ml",
    "category": "OBAT",
    "unit": "SCHT",
    "selling_price": 4000,
    "hpp": 3200,
    "stock": 1,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Broad Plus 40gr",
    "category": "OBAT",
    "unit": "SCHT",
    "selling_price": 12500,
    "hpp": 10000,
    "stock": 1411,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Buldok 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 26000,
    "hpp": 20800,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Buldok 250ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 56000,
    "hpp": 44800,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Buldok 500 ml NEW",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 105000,
    "hpp": 84000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Tandem 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 125000,
    "hpp": 100000,
    "stock": 16,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Calans 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 280000,
    "hpp": 224000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Calans 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 79000,
    "hpp": 63200,
    "stock": 8,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Calans 500ml NEW",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 143000,
    "hpp": 114400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Calsium Kumbang 1 kg",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 12500,
    "hpp": 10000,
    "stock": 1,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "HUMICID Asam Humat 1 kg",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pupuk Cantik 50 kg",
    "category": "OBAT",
    "unit": "ZAK",
    "selling_price": 875000,
    "hpp": 700000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Trutone 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 100000,
    "hpp": 80000,
    "stock": 32,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "CALSIPRO 1 kg",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 18000,
    "hpp": 14400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "CASTORA 100 gram",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 22,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "CASTORA 15 gr",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 6500,
    "hpp": 5200,
    "stock": 198,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Prima Bajen 200 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 20,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "STARMIN 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 98000,
    "hpp": 78400,
    "stock": 177,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bablas NEW + Mets 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 55000,
    "hpp": 44000,
    "stock": 608,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bactocyn 200 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 69000,
    "hpp": 55200,
    "stock": 22,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bactoplus Padi",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 10000,
    "hpp": 8000,
    "stock": 63,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "GLUFO 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 65000,
    "hpp": 52000,
    "stock": 49,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Balistic 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 72,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Balistic 200 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 65000,
    "hpp": 52000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Balistic 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 154000,
    "hpp": 123200,
    "stock": 57,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Barrier 1 kg NEW",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 30000,
    "hpp": 24000,
    "stock": 6,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Basmirata 50 gr",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 12500,
    "hpp": 10000,
    "stock": 162,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bassa 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 13000,
    "hpp": 10400,
    "stock": 65,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bassa 400 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 39000,
    "hpp": 31200,
    "stock": 58,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Baycarb 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 77000,
    "hpp": 61600,
    "stock": 20,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bayfolan 250ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 19000,
    "hpp": 15200,
    "stock": 35,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "BELT EXPERT 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 330000,
    "hpp": 264000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "BELT EXPERT 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 140000,
    "hpp": 112000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pupuk Unggul Multifungsi 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 100000,
    "hpp": 80000,
    "stock": 8,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Besun Elite 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 110000,
    "hpp": 88000,
    "stock": 29,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Biggest 10 ml",
    "category": "OBAT",
    "unit": "SCHT",
    "selling_price": 15000,
    "hpp": 12000,
    "stock": 138,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Big Grow 20 WP/5 gr",
    "category": "OBAT",
    "unit": "SCHT",
    "selling_price": 48000,
    "hpp": 38400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Biocron 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Biocron 200 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 44000,
    "hpp": 35200,
    "stock": 4,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Biocron 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 98000,
    "hpp": 78400,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bionasa 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 59000,
    "hpp": 47200,
    "stock": 162,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bionasa 2 lt",
    "category": "OBAT",
    "unit": "JRGN",
    "selling_price": 117000,
    "hpp": 93600,
    "stock": 13,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bionasa 200 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 20000,
    "hpp": 16000,
    "stock": 6,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bionasa 4 lt",
    "category": "OBAT",
    "unit": "JRGN",
    "selling_price": 230000,
    "hpp": 184000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Bionasa 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 28000,
    "hpp": 22400,
    "stock": 12,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Antracol 1 kg NEW",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 165000,
    "hpp": 132000,
    "stock": 84,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Antracol 250 gr NEW",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 44000,
    "hpp": 35200,
    "stock": 103,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Antracol 500 gr NEW",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 85000,
    "hpp": 68000,
    "stock": 80,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Anvil 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 70000,
    "hpp": 56000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Applaud 400 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 55000,
    "hpp": 44000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Applaud 100 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 15000,
    "hpp": 12000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Apsa 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 150000,
    "hpp": 120000,
    "stock": 17,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Ares 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 28000,
    "hpp": 22400,
    "stock": 26,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Ares 300 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 75000,
    "hpp": 60000,
    "stock": 2,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Ares 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 120000,
    "hpp": 96000,
    "stock": 19,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Arjuna 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 130000,
    "hpp": 104000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Arrivo 100 ml NEW",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 12000,
    "hpp": 9600,
    "stock": 308,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Arrivo 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 23000,
    "hpp": 18400,
    "stock": 111,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Arrivo 500 ml NEW",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 39000,
    "hpp": 31200,
    "stock": 120,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Asmec 50 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 50000,
    "hpp": 40000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Aster 1 lt",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 220000,
    "hpp": 176000,
    "stock": 9,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Astertrin 400 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 80000,
    "hpp": 64000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Astonish 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 24000,
    "hpp": 19200,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "ATNIMES 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 170000,
    "hpp": 136000,
    "stock": 3,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Atonik 100 ml NEW",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 19000,
    "hpp": 15200,
    "stock": 44,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Atonik 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 65000,
    "hpp": 52000,
    "stock": 12,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Amexone 500 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 62000,
    "hpp": 49600,
    "stock": 41,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jaya Top 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 80000,
    "hpp": 64000,
    "stock": 10,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Aurora 100 gr",
    "category": "OBAT",
    "unit": "PCS",
    "selling_price": 7500,
    "hpp": 6000,
    "stock": 2,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Aurora 800 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 85000,
    "hpp": 68000,
    "stock": 3,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "AVATAR 100 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 105000,
    "hpp": 84000,
    "stock": 19,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Avidor 100 gr",
    "category": "OBAT",
    "unit": "PAK",
    "selling_price": 28000,
    "hpp": 22400,
    "stock": 217,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Extar 250 ml",
    "category": "OBAT",
    "unit": "BTL",
    "selling_price": 37000,
    "hpp": 29600,
    "stock": 26,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "WAGGONER Brake Fluid 50ml",
    "category": "OLI",
    "unit": "PCS",
    "selling_price": 5000,
    "hpp": 4000,
    "stock": 187,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Agip/Ride 2T",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 32000,
    "hpp": 25600,
    "stock": 108,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "AHM MPX1 0,8lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 73000,
    "hpp": 58400,
    "stock": 162,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "AHM MPX1 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 85000,
    "hpp": 68000,
    "stock": 120,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "AHM MPX2 0,8lt",
    "category": "OLI",
    "unit": "PCS",
    "selling_price": 76000,
    "hpp": 60800,
    "stock": 366,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Ahm Gardan",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 20000,
    "hpp": 16000,
    "stock": 198,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Oli Drum TREX Sae50 /CF",
    "category": "OLI",
    "unit": "LT",
    "selling_price": 42000,
    "hpp": 33600,
    "stock": 271,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Deltalube Daily 15W-40/4 lt",
    "category": "OLI",
    "unit": "GLN",
    "selling_price": 530000,
    "hpp": 424000,
    "stock": 4,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Waggoner Carbu Cleaner 500 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 32000,
    "hpp": 25600,
    "stock": 29,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Castrol 2T/LS/NEW 700 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 50000,
    "hpp": 40000,
    "stock": 657,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Castrol 4T/20W-40/ 800 ml NEW",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 49000,
    "hpp": 39200,
    "stock": 38,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Castrol Power 4T 800 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 1,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Master Chain Lube 150 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 23000,
    "hpp": 18400,
    "stock": 36,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Deltalube Daily 15W-40 / 1lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 138000,
    "hpp": 110400,
    "stock": 9,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Deltalube Multi sae 40/ 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 112000,
    "hpp": 89600,
    "stock": 19,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Unirace 4T 20W-50/0,8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 12,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Deltalube Multi sae 40/5 lt",
    "category": "OLI",
    "unit": "GLN",
    "selling_price": 525000,
    "hpp": 420000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Drum Kosong",
    "category": "LAIN-LAIN",
    "unit": "PCS",
    "selling_price": 175000,
    "hpp": 140000,
    "stock": 5,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Oli Drum UNIRACE Sae 40/lt",
    "category": "OLI",
    "unit": "LT",
    "selling_price": 32000,
    "hpp": 25600,
    "stock": 1374,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "ECCO 20W-50/KUNING/0,8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "MAX 1/0,8 lt Merah",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 64,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Enduro Matic S/ 0,8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 58000,
    "hpp": 46400,
    "stock": 32,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Enduro Matic G/ 0,8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 59000,
    "hpp": 47200,
    "stock": 68,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Enduro Racing 4T 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 77000,
    "hpp": 61600,
    "stock": 67,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "MAX 2/0,8 lt Biru",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 192,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "ECSTAR 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 57000,
    "hpp": 45600,
    "stock": 32,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Shell AX5 4T Kuning 15W-40 /0,8lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 125,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Shell SX 2T/0,8lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 55000,
    "hpp": 44000,
    "stock": 6,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Shell Helix HX3 4T Merah /1lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 10,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Shell AX7 Matic Biru 10W-30 /0,8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 75000,
    "hpp": 60000,
    "stock": 59,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Shell RIMULA R4/ 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 75000,
    "hpp": 60000,
    "stock": 9,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Shell RIMULA R4/ 5 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 350000,
    "hpp": 280000,
    "stock": 10,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Kompon Putih",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 10000,
    "hpp": 8000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Ultratec 4T 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 70000,
    "hpp": 56000,
    "stock": 75,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "WD 40 220 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 70000,
    "hpp": 56000,
    "stock": 20,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Top 1 HP SPORT 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 62000,
    "hpp": 49600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Top 1 MC 0.8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 49000,
    "hpp": 39200,
    "stock": 42,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Top 1 MC 1 lt NEW",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 59000,
    "hpp": 47200,
    "stock": 21,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Idemitsu 2T/ 0,7 lt NEW",
    "category": "OLI",
    "unit": "KLG",
    "selling_price": 57000,
    "hpp": 45600,
    "stock": 495,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Ultraline 4T/ 0.8L",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 305,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "UNIRACE 4T 20 lt",
    "category": "OLI",
    "unit": "GLN",
    "selling_price": 435000,
    "hpp": 348000,
    "stock": 4,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "UNIRACE sae 140/ 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 32000,
    "hpp": 25600,
    "stock": 817,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "UNIRACE sae 90/ 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 32000,
    "hpp": 25600,
    "stock": 96,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Wagner",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 30000,
    "hpp": 24000,
    "stock": 9,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Yamalube 4T 0,8 L",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 58000,
    "hpp": 46400,
    "stock": 147,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Yamalube Gardan",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 19000,
    "hpp": 15200,
    "stock": 90,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Yamalube XP/ 0,8L",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 50000,
    "hpp": 40000,
    "stock": 238,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Yamalube Matic / 0,8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 58000,
    "hpp": 46400,
    "stock": 656,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Yamalube Sport 4T 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 67000,
    "hpp": 53600,
    "stock": 65,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Yamalube Super Matic / 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 82000,
    "hpp": 65600,
    "stock": 53,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "AHM MPX 2/0,65lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 65000,
    "hpp": 52000,
    "stock": 76,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "RJ Rust Guard 150 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 14500,
    "hpp": 11600,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Rored HDA 90/4 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 220000,
    "hpp": 176000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Rored EPA140/4 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 226000,
    "hpp": 180800,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Ecco 2T Hijau 700 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 30000,
    "hpp": 24000,
    "stock": 59,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "ECSTAR 4T/0,8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 47000,
    "hpp": 37600,
    "stock": 61,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Shell Helix HX3 4TMerah /1lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 10,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Shell Helix HX5 / 1lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 90000,
    "hpp": 72000,
    "stock": 103,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Shell RIMULA R4/ 1lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 75000,
    "hpp": 60000,
    "stock": 9,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Ultratec 4T 0,8L",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 59000,
    "hpp": 47200,
    "stock": 566,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Idemitsu 2T/0,7 lt NEW",
    "category": "OLI",
    "unit": "KLG",
    "selling_price": 57000,
    "hpp": 45600,
    "stock": 495,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "UNIRACE sae 140/1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 32000,
    "hpp": 25600,
    "stock": 817,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "UNIRACE sae 90/1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 32000,
    "hpp": 25600,
    "stock": 96,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Meditran SX 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 71000,
    "hpp": 56800,
    "stock": 60,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Meditran SX 10 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 650000,
    "hpp": 520000,
    "stock": 5,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Meditran SX 4 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 267000,
    "hpp": 213600,
    "stock": 15,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "MIZU Carb Cleaner 500 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 22000,
    "hpp": 17600,
    "stock": 27,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mesrania 2T OB 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 53000,
    "hpp": 42400,
    "stock": 90,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mesrania 2T Super 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 64000,
    "hpp": 51200,
    "stock": 21,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mesran B40 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 60000,
    "hpp": 48000,
    "stock": 393,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mesran B40 10 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 545000,
    "hpp": 436000,
    "stock": 1,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mesran B40 4 Lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 220000,
    "hpp": 176000,
    "stock": 94,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mesran B40 5 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 275000,
    "hpp": 220000,
    "stock": 33,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mesran Super 0.8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 49000,
    "hpp": 39200,
    "stock": 315,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mesran Super 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 58000,
    "hpp": 46400,
    "stock": 358,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Mesran Super 4 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 220000,
    "hpp": 176000,
    "stock": 14,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "ECCO PCX 1 0.8L",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 126,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jumbo Rem 300 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 22000,
    "hpp": 17600,
    "stock": 295,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Orange 2T ORANGE",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 30000,
    "hpp": 24000,
    "stock": 41,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pet Brake Fluid 50 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 6000,
    "hpp": 4800,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pet Carb Clean 500 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 30000,
    "hpp": 24000,
    "stock": 20,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pet Chain Lube 300 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 23000,
    "hpp": 18400,
    "stock": 88,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pledge Orange/Lemon 160 gr",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 25000,
    "hpp": 20000,
    "stock": 32,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Prestone 300 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 33000,
    "hpp": 26400,
    "stock": 163,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Prima XP New 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 61000,
    "hpp": 48800,
    "stock": 100,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Prima XP 4 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 233000,
    "hpp": 186400,
    "stock": 22,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Repsol MXR3/0,8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 46000,
    "hpp": 36800,
    "stock": 355,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Repsol MATIC /0,8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 46000,
    "hpp": 36800,
    "stock": 38,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Repsol MXR3/1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 58000,
    "hpp": 46400,
    "stock": 11,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "RED Gas 230gr",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 24000,
    "hpp": 19200,
    "stock": 3,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Enduro 4T New 0,8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 53000,
    "hpp": 42400,
    "stock": 160,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Evalube 2T",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 32000,
    "hpp": 25600,
    "stock": 978,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Evalube 2T PRO",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 40000,
    "hpp": 32000,
    "stock": 480,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Evalube 4T",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 39000,
    "hpp": 31200,
    "stock": 1287,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Federal Matic ORANGE 0,8 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 52000,
    "hpp": 41600,
    "stock": 772,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pet Grease BIRU 100 gr",
    "category": "OLI",
    "unit": "PCS",
    "selling_price": 15000,
    "hpp": 12000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "RYUSEI 4T 0,8L",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 219,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Shell Helix HX5 / 4 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 350000,
    "hpp": 280000,
    "stock": 41,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "JOT 140 / 1lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 884,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "JOT 90 / 1lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 86,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jumbo Rad Coolant 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 16000,
    "hpp": 12800,
    "stock": 341,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jumbo DX 40 / 5 lt",
    "category": "OLI",
    "unit": "JRGN",
    "selling_price": 195000,
    "hpp": 156000,
    "stock": 37,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jumbo PSF 500 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 27000,
    "hpp": 21600,
    "stock": 19,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "JUMBO SKOK",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 11000,
    "hpp": 8800,
    "stock": 344,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Jumbo DX 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 44000,
    "hpp": 35200,
    "stock": 215,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "UNIRACE HD 68 Hydraulic /20 lt",
    "category": "OLI",
    "unit": "JRGN",
    "selling_price": 435000,
    "hpp": 348000,
    "stock": 14,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Orange 2T HITAM",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 35000,
    "hpp": 28000,
    "stock": 442,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "DEGRASER REXCO 70 500ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 48000,
    "hpp": 38400,
    "stock": 2,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "KIT Chain Lube BIRU 110 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 26000,
    "hpp": 20800,
    "stock": 12,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Pet Full Penetrating 300 ml",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 23000,
    "hpp": 18400,
    "stock": 24,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Meditran SC 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 66000,
    "hpp": 52800,
    "stock": 45,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Meditran S 1 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 62000,
    "hpp": 49600,
    "stock": 132,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Meditran S 10 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 550000,
    "hpp": 440000,
    "stock": 10,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Meditran S 5 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 280000,
    "hpp": 224000,
    "stock": 44,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Meditran SC 10 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 600000,
    "hpp": 480000,
    "stock": 8,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Meditran SC 5 lt",
    "category": "OLI",
    "unit": "BTL",
    "selling_price": 303000,
    "hpp": 242400,
    "stock": 41,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Roll Padi PADDY HORSE SUPER",
    "category": "ROLL",
    "unit": "PCS",
    "selling_price": 305000,
    "hpp": 244000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Roll Padi Super Camel",
    "category": "ROLL",
    "unit": "PCS",
    "selling_price": 315000,
    "hpp": 252000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Roll Padi Super Kijang K",
    "category": "ROLL",
    "unit": "PCS",
    "selling_price": 185000,
    "hpp": 148000,
    "stock": 1,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Roll Padi Agindo XL",
    "category": "ROLL",
    "unit": "PCS",
    "selling_price": 335000,
    "hpp": 268000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Roll Padi YANMAR",
    "category": "ROLL",
    "unit": "PCS",
    "selling_price": 510000,
    "hpp": 408000,
    "stock": 8,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 2.5x100mt HT DL",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 190000,
    "hpp": 152000,
    "stock": 17,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 2.5x100mt HT GAJAH",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 250000,
    "hpp": 200000,
    "stock": 11,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 2.5x100mt PT DL",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 140000,
    "hpp": 112000,
    "stock": 9,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 2.5x50mt HT DL",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 95000,
    "hpp": 76000,
    "stock": 43,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 2.5x50mt HT GAJAH",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 125000,
    "hpp": 100000,
    "stock": 12,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 2.5x50mt HT SINGA",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 135000,
    "hpp": 108000,
    "stock": 46,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 2.5x50mt HT BANDUNG",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 85000,
    "hpp": 68000,
    "stock": 1,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 2.5x50mt PT DL",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 70000,
    "hpp": 56000,
    "stock": 8,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 2x100mt HT DL",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 160000,
    "hpp": 128000,
    "stock": 1,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 2x100mt HT GAJAH",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 210000,
    "hpp": 168000,
    "stock": 2,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Karet 2x50 mt TIGER RED",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 350000,
    "hpp": 280000,
    "stock": 7,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 2x50mt HT DL",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 80000,
    "hpp": 64000,
    "stock": 6,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 2x50mt HT GAJAH",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 105000,
    "hpp": 84000,
    "stock": 15,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Karet 2x50 mt KATO",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 375000,
    "hpp": 300000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Karet 2x50 mt ProQuip",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 400000,
    "hpp": 320000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 3x100mt HT DL",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 230000,
    "hpp": 184000,
    "stock": 44,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 3x100mt HT GAJAH",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 300000,
    "hpp": 240000,
    "stock": 23,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Karet 3x100mt TIGER RED",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 875000,
    "hpp": 700000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 3x100mt PT DL",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 180000,
    "hpp": 144000,
    "stock": 17,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 3x50mt HT DL",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 115000,
    "hpp": 92000,
    "stock": 224,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 3x50mt HT SINGA",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 165000,
    "hpp": 132000,
    "stock": 17,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Karet 3x50 mt KATO",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 525000,
    "hpp": 420000,
    "stock": 0,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Karet 3x50 mt ProQuip",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 575000,
    "hpp": 460000,
    "stock": 25,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 3x50mt PT DL",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 90000,
    "hpp": 72000,
    "stock": 8,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Karet 3x50 mt TIGER RED",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 470000,
    "hpp": 376000,
    "stock": 29,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 4x100mt HT DL",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 310000,
    "hpp": 248000,
    "stock": 24,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Slg Plas 4x100mt HT GAJAH",
    "category": "SLG",
    "unit": "ROLL",
    "selling_price": 390000,
    "hpp": 312000,
    "stock": 5,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Terpal 2x3/A5/Tiger",
    "category": "TERPAL",
    "unit": "PCS",
    "selling_price": 37500,
    "hpp": 30000,
    "stock": 14,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Terpal 3x4/A5/Tiger",
    "category": "TERPAL",
    "unit": "PCS",
    "selling_price": 75000,
    "hpp": 60000,
    "stock": 6,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Terpal 3x5/A5/Tiger",
    "category": "TERPAL",
    "unit": "PCS",
    "selling_price": 93000,
    "hpp": 74400,
    "stock": 8,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Terpal 4x6/A5/Tiger",
    "category": "TERPAL",
    "unit": "PCS",
    "selling_price": 149000,
    "hpp": 119200,
    "stock": 36,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Terpal 5x7/A5/Tiger",
    "category": "TERPAL",
    "unit": "PCS",
    "selling_price": 217000,
    "hpp": 173600,
    "stock": 5,
    "minimum_stock": 10,
    "is_active": true
  },
  {
    "product_name": "Terpal 6x8/A5/Tiger",
    "category": "TERPAL",
    "unit": "PCS",
    "selling_price": 295000,
    "hpp": 236000,
    "stock": 5,
    "minimum_stock": 10,
    "is_active": true
  }
]
JSON;
    }

    private function customersJson(): string
    {
        return <<<'JSON'
[
  {
    "full_name": "NUR KAYANTO",
    "whatsapp_number": "082131375955",
    "address": "NgeblaK, RT 01/RW 05, KEDUNGGUDEL, WIDODAREN, NGAWI",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "JOKO KUNCORO MANISHARJO",
    "whatsapp_number": "082143676736",
    "address": "Dusun Srikaton Rt03 Rw04",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "AGUS SUGIYONO",
    "whatsapp_number": "085230054874",
    "address": "KUNCEN RT05/RW03, SIDOMAKMUR.",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "KEYLA MOTOR",
    "whatsapp_number": "085337448227",
    "address": "KAUMAN. GRENTENG",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "JUANTO Bengkel Mendiro",
    "whatsapp_number": "081252295359",
    "address": "TAWANG RT 02 MENDIRO",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "ANTOK",
    "whatsapp_number": "08813275911",
    "address": "Gembol",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "SRI REJEKI TAMBAKSELO",
    "whatsapp_number": "082141661859",
    "address": "Tambakselo",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "BENGKEL AAN / TAKSEN",
    "whatsapp_number": "08125956999",
    "address": "Walikukun",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "MARUF TRI NUGROHO",
    "whatsapp_number": "0895620042690",
    "address": "KAUMAN, WIDODAREN",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "TEGUH PRASETYO",
    "whatsapp_number": "085784450934",
    "address": "Rt01 Rw01 Pengkol",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "NANDA PUTRA KHRISNADI",
    "whatsapp_number": "087767761925",
    "address": "KEC WIDODAREN RT 04 RW 04",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "EKO CAHYONO",
    "whatsapp_number": "087743426196",
    "address": "MENGGER RT01/RW02",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "INDY KUSMAWAN",
    "whatsapp_number": "082232246224",
    "address": "Gendingan. Kauman",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "ABDULAH KHORIB",
    "whatsapp_number": "082108548772",
    "address": "Nglongkeh, RT02 RW 09",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "ANANG WAHYUDI",
    "whatsapp_number": "085736790889",
    "address": "NGELO RT03 RW01",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "SUTARNO",
    "whatsapp_number": "08573681777",
    "address": "Jenak. Banyubiru",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "DUWI PRISTIWA",
    "whatsapp_number": "081515055383",
    "address": "RT01 RW04 Sıdolaju, Widodaren",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "SUDARTO Karangbanyu",
    "whatsapp_number": "082327726677",
    "address": "KARANGBANYU,WIDODAREN",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "Mbak NANIK",
    "whatsapp_number": "081234376976",
    "address": "Sidolaju",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "AGENG PRASOJO",
    "whatsapp_number": "081776585078",
    "address": "JENAK BANYUBIRU",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "MERRITA",
    "whatsapp_number": "081387557559",
    "address": "RT01 RW05 Sekaralas, Widodaren",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "VIRGA",
    "whatsapp_number": "082140812494",
    "address": "Kedunggalar",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "Bp SANTOSO",
    "whatsapp_number": "081335708444",
    "address": "Sekarputih, Kenongorejo",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "ZONA BAN",
    "whatsapp_number": "085646336894",
    "address": "WALIKUKUN WETAN RT01/RW04",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "SORIATI SITANGGANG",
    "whatsapp_number": "082225298776",
    "address": "JATEN RT014 RW04, TOYOGO SAMBUNGMACAN",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "Bp NARTO",
    "whatsapp_number": "082141874321",
    "address": "KARANGMALANG",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "Bp NURYANTO",
    "whatsapp_number": "085706772777",
    "address": "Ngrambe",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "Bp NUR KHOLIQ",
    "whatsapp_number": "085855696683",
    "address": "Kedungdowo",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "NUR SAPUTRO YULIANTO",
    "whatsapp_number": "0823344974274",
    "address": "GILIS RT06/RW01",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "DEWI FATMAWAT",
    "whatsapp_number": "085749014854",
    "address": "Kauman",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "SUHARIYANTO",
    "whatsapp_number": "085773582001",
    "address": "PelangLor Kedunggalar",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "Bp PEDRO",
    "whatsapp_number": "085235808889",
    "address": "JL PAYAK NO 10",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "SUMINGAN",
    "whatsapp_number": "085921981106",
    "address": "MANTINGAN",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "RONI WARIH UTOMO",
    "whatsapp_number": "085806173599",
    "address": "RT01 RW02 Jatisari",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "Bengkel ARDI MOTOR",
    "whatsapp_number": "085895091208",
    "address": "Pojok 001/002 GALEH",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "JUMIANA",
    "whatsapp_number": "082139677567",
    "address": "Kebonagung, Sekarputih",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "IKROM KAHARUDIN",
    "whatsapp_number": "081217713837",
    "address": "SEKARPUTIH, WIDODAREN",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "AGUS MUKTASIM",
    "whatsapp_number": "085784131980",
    "address": "Jenak Banyubiru RT07 RW02",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "Bengkel DHOLOG STD",
    "whatsapp_number": "082244810528",
    "address": "Kuncen RT08 RW04 Bulakpepe",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "KOMENG / Sugeng Riyanto",
    "whatsapp_number": "085850362375",
    "address": "Kawis Rt03 Rw03",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "MUHSIN Bengkel Gendingan",
    "whatsapp_number": "085233593112",
    "address": "RT01 RW 06 Gendingan",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "ANNISA FITRI JUNED",
    "whatsapp_number": "081334785138",
    "address": "Kepanjen Kidul",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "ROUF SATRIA GYMNASTIAR",
    "whatsapp_number": "085730276720",
    "address": "Gnedingan, Widodaren",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "BUDIYANTO di",
    "whatsapp_number": "082229342621",
    "address": "Bangkleyan, Jati, Blora",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "SUWADI",
    "whatsapp_number": "081399151461",
    "address": "SEKARALAS RT02/RW05",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  },
  {
    "full_name": "ANDRI SUSANTO",
    "whatsapp_number": "085895322866",
    "address": "Sidolaju RT08 RW04",
    "tier": "bronze",
    "total_accumulation": 0,
    "point_balance": 0
  }
]
JSON;
    }

    private function suppliersJson(): string
    {
        return <<<'JSON'
[
  {
    "name": "Petrokimia Kayaku",
    "address": "Masaran, Sragen, Jawa Tengah",
    "contact_person": "Admin Petrokimia Kayaku",
    "phone": "081325602530"
  },
  {
    "name": "Petrosida Gresik",
    "address": "Sidoarjo, Jawa Timur",
    "contact_person": "Sony P.",
    "phone": "081395680395"
  },
  {
    "name": "Pasti Hasil",
    "address": "Jl. Mataram Utara, Solo",
    "contact_person": "Admin Pasti Hasil",
    "phone": "0271056899"
  },
  {
    "name": "Payung Agung",
    "address": "Solo, Jawa Tengah",
    "contact_person": "Admin Payung Agung",
    "phone": "081326475350"
  },
  {
    "name": "Pelita Tani Ponorogo",
    "address": "Ponorogo, Jawa Timur",
    "contact_person": "Admin Pelita Tani",
    "phone": "081325602530"
  },
  {
    "name": "PT Agro Cemerlang Plasindo",
    "address": "Jl. KHR Abdul Fatah, Blitar",
    "contact_person": "Admin Plasindo",
    "phone": "081357562770"
  },
  {
    "name": "PT Mega Jaya Net",
    "address": "Kediri, Jawa Timur",
    "contact_person": "Admin Mega Jaya",
    "phone": "081234540921"
  },
  {
    "name": "Mitra Agro Lestari",
    "address": "Semarang, Jawa Tengah",
    "contact_person": "Andre C.",
    "phone": "081331118085"
  },
  {
    "name": "Mitra Agro Sukses",
    "address": "Jawa Timur",
    "contact_person": "Bagos",
    "phone": "081326668586"
  },
  {
    "name": "Multi Jaya Makmur",
    "address": "Ngawi, Jawa Timur",
    "contact_person": "Fauzi",
    "phone": "081239299561"
  },
  {
    "name": "Oriental Seed",
    "address": "Jogja, DI Yogyakarta",
    "contact_person": "Admin Oriental Seed",
    "phone": "081334569586"
  },
  {
    "name": "NTM Seed",
    "address": "Surabaya, Jawa Timur",
    "contact_person": "Trias",
    "phone": "081232555675"
  },
  {
    "name": "MEY Paranet Bandung",
    "address": "Bandung, Jawa Barat",
    "contact_person": "Admin MEY Paranet",
    "phone": "082127638767"
  },
  {
    "name": "Mulsa Prima Ronanda",
    "address": "Surabaya, Jawa Timur",
    "contact_person": "Admin Mulsa Prima",
    "phone": "081230909651"
  },
  {
    "name": "Plastik 100 Solo",
    "address": "Solo, Jawa Tengah",
    "contact_person": "Admin Plastik 100",
    "phone": "081234540921"
  },
  {
    "name": "Pinarak Diesel",
    "address": "Gresik, Jawa Timur",
    "contact_person": "DW",
    "phone": "081239450921"
  },
  {
    "name": "Prima Karya",
    "address": "Jl. Veteran 203, Solo",
    "contact_person": "Admin Prima Karya",
    "phone": "0271646370"
  },
  {
    "name": "PT Sumber Baru Ban",
    "address": "Jl. MT Haryono 662, Semarang",
    "contact_person": "Pak Tau",
    "phone": "0243539445"
  }
]
JSON;
    }
}
