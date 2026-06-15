@extends('layouts.app')
@section('title','Kasir / POS')
@section('page_title','Kasir / POS')

@push('styles')
<style>
    .pos-wrap { display:flex; gap:20px; align-items:flex-start; }
    .pos-left { flex:1; min-width:0; }
    .pos-right { width:360px; flex-shrink:0; position:sticky; top:82px; }
    .product-result { position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #e5e7eb; border-top:none; border-radius:0 0 10px 10px; z-index:100; box-shadow:0 8px 24px rgba(0,0,0,.12); max-height:320px; overflow-y:auto; }
    .product-item { padding:10px 14px; cursor:pointer; border-bottom:1px solid #f3f4f6; transition:.15s; display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .product-item:hover { background:var(--primary-pale); }
    .product-item:last-child { border-bottom:none; }
    .product-item .muted-line { font-size:.74rem;color:#6b7280;line-height:1.35; }
    .cart-table td, .cart-table th { font-size:.82rem; vertical-align:middle; }
    .customer-card { background:var(--primary-pale); border-radius:10px; padding:12px 14px; margin-bottom:12px; }
    .promo-badge { display:inline-flex; align-items:center; gap:4px; background:#fee2e2; color:#991b1b; font-size:.7rem; font-weight:700; padding:2px 8px; border-radius:10px; }
    .member-badge { display:inline-flex; align-items:center; gap:4px; background:#d1fae5; color:#065f46; font-size:.7rem; font-weight:700; padding:2px 8px; border-radius:10px; }
    .hpp-warning-badge { display:inline-flex; align-items:center; gap:4px; background:#fef3c7; color:#92400e; font-size:.7rem; font-weight:700; padding:2px 8px; border-radius:10px; margin-top:4px; }
    .price-warning { font-size:.7rem;color:#b45309;margin-top:4px;line-height:1.25; }
    .redeem-box { display:none; background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:10px 12px; margin:10px 0; }
    .under-hpp-box { display:none; background:#fff7ed; border:1px solid #fdba74; border-radius:10px; padding:10px 12px; margin:10px 0; }
    .under-hpp-box .title { font-weight:800;color:#9a3412;font-size:.82rem; }
    .under-hpp-box .desc { color:#9a3412;font-size:.75rem;line-height:1.35; }
    .payment-only { display:none; }
    .payment-review { display:none; border:1px solid #e5e7eb; background:#f9fafb; border-radius:12px; padding:12px; margin:12px 0; }
    .review-item { display:flex; justify-content:space-between; gap:8px; font-size:.78rem; padding:5px 0; border-bottom:1px dashed #e5e7eb; }
    .review-item:last-child { border-bottom:none; }
    @media(max-width:900px) { .pos-wrap { flex-direction:column; } .pos-right { width:100%; position:static; } }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('kasir.store') }}" id="pos-form">
@csrf
<input type="hidden" name="discount_percent" id="discount_percent" value="0">

<!-- Kontainer Utama POS -->
<div class="pos-wrap">

    <!-- Bagian Kiri: Pencarian dan Keranjang -->
    <div class="pos-left">
        <!-- Kartu Modul Pelanggan -->
        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-person-check me-2" style="color:var(--primary)"></i>Pelanggan</h6></div>
            <div class="card-body pb-3">
                <div id="customer-display" style="display:none" class="customer-card">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div class="min-w-0">
                            <div style="font-weight:700;font-size:.9rem" id="cust-name"></div>
                            <div style="font-size:.78rem;color:#374151">
                                <span id="cust-tier-badge"></span>
                                <span id="cust-phone" class="ms-2"></span>
                            </div>
                            <div id="cust-address" style="font-size:.75rem;color:#6b7280;margin-top:3px;display:none"></div>
                            <div style="font-size:.76rem;color:var(--primary);margin-top:3px">
                                Saldo Poin: <strong id="cust-points"></strong> &nbsp;|&nbsp; Diskon Member: <strong id="cust-discount"></strong>%
                            </div>
                        </div>
                        <div class="d-flex gap-2 align-items-center flex-shrink-0">
                            <span id="cust-wa-btn" style="display:none">
                                <a href="#" target="_blank" id="cust-wa-link"
                                   class="btn btn-sm px-3"
                                   style="background:#25d366;color:#fff;border:none;font-weight:600;font-size:.76rem">
                                    <i class="bi bi-whatsapp me-1"></i>Info Membership
                                </a>
                            </span>
                            <button type="button" onclick="clearCustomer()" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="customer-search-wrap">
                    <div style="position:relative">
                        <div class="search-bar">
                            <i class="bi bi-search si-search"></i>
                            <input type="text" id="customer-search" class="form-control"
                                   placeholder="Cari member (nama / WhatsApp / alamat)..." autocomplete="off">
                        </div>
                        <div id="customer-results" class="product-result" style="display:none"></div>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('customers.create', ['return_to' => 'kasir']) }}" onclick="goRegisterMemberFromPos(event)" style="font-size:.78rem;color:var(--primary)">
                            <i class="bi bi-person-plus me-1"></i>Daftarkan member baru
                        </a>
                        <span style="font-size:.78rem;color:#9ca3af;margin-left:8px">atau lanjutkan tanpa member</span>
                    </div>
                </div>
                <input type="hidden" name="customer_id" id="customer_id" value="">
            </div>
        </div>

        <!-- Kartu Modul Pencarian Produk -->
        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-search me-2" style="color:var(--primary)"></i>Cari Produk</h6></div>
            <div class="card-body pb-3">
                <div style="position:relative">
                    <div class="search-bar">
                        <i class="bi bi-search si-search"></i>
                        <input type="text" id="product-search" class="form-control"
                               placeholder="Ketik nama produk..." autocomplete="off">
                    </div>
                    <div id="product-results" class="product-result" style="display:none"></div>
                </div>
            </div>
        </div>

        <!-- Kartu Modul Keranjang Belanja -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="bi bi-cart3 me-2" style="color:var(--primary)"></i>Keranjang Belanja</h6>
                <div class="d-flex align-items-center gap-3">
                    <button type="button" onclick="emptyCart()" class="btn btn-sm btn-outline-danger" style="font-size:.75rem; font-weight:600; padding: 4px 10px; border-radius:6px;">
                        <i class="bi bi-trash me-1"></i>Kosongkan Keranjang
                    </button>
                    <span id="cart-count" style="font-size:.78rem;color:#9ca3af;white-space:nowrap;">0 item</span>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="table cart-table mb-0">
                    <thead>
                        <tr>
                            <th style="cursor:pointer; white-space:nowrap" onclick="sortCart('product_name')">Produk <span id="sort-icon-product_name"><i class="bi bi-arrow-down-up text-muted" style="font-size:0.8em;opacity:0.4"></i></span></th>
                            <th style="cursor:pointer; white-space:nowrap" onclick="sortCart('final_unit_price')">Harga Satuan <span id="sort-icon-final_unit_price"><i class="bi bi-arrow-down-up text-muted" style="font-size:0.8em;opacity:0.4"></i></span></th>
                            <th style="cursor:pointer; white-space:nowrap" onclick="sortCart('qty')">Qty <span id="sort-icon-qty"><i class="bi bi-arrow-down-up text-muted" style="font-size:0.8em;opacity:0.4"></i></span></th>
                            <th style="cursor:pointer; white-space:nowrap" onclick="sortCart('subtotal')">Subtotal <span id="sort-icon-subtotal"><i class="bi bi-arrow-down-up text-muted" style="font-size:0.8em;opacity:0.4"></i></span></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <tr>
                            <td colspan="5" class="text-center py-4" style="color:#9ca3af">
                                <i class="bi bi-cart" style="font-size:2rem;display:block;margin-bottom:6px"></i>
                                Belum ada produk dipilih
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bagian Kanan: Ringkasan Pembayaran -->
    <div class="pos-right">
        <!-- Kartu Ringkasan Pembayaran -->
        <div class="card">
            <div class="card-header"><h6><i class="bi bi-calculator me-2" style="color:var(--primary)"></i>Ringkasan Pembayaran</h6></div>
            <div class="card-body">

                <div class="d-flex justify-content-between mb-1" style="font-size:.84rem">
                    <span style="color:#6b7280">Subtotal</span>
                    <span id="summary-subtotal" style="font-weight:600">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:.84rem;display:none" id="discount-row">
                    <span style="color:#6b7280">Diskon Member (<span id="disc-pct">0</span>%)</span>
                    <span id="summary-discount" style="font-weight:600;color:#dc2626">-Rp 0</span>
                </div>
                <div id="promo-note" style="display:none;font-size:.76rem;color:#dc2626;margin-bottom:6px">
                    <i class="bi bi-tag-fill me-1"></i>Ada item berpromo — diskon member tidak berlaku pada item tersebut
                </div>

                <div id="under-hpp-box" class="under-hpp-box">
                    <div class="title"><i class="bi bi-exclamation-triangle me-1"></i>Harga di Bawah HPP</div>
                    <div id="under-hpp-message" class="desc mt-1"></div>
                    <div id="under-hpp-auth-fields" style="display:none" class="mt-2">
                        <label class="form-label mb-1" style="font-size:.76rem">Otorisasi Admin/Pemilik</label>
                        <input type="text" name="under_hpp_admin_email" id="under_hpp_admin_email" class="form-control form-control-sm mb-2" placeholder="Username/email admin">
                        <input type="password" name="under_hpp_admin_password" id="under_hpp_admin_password" class="form-control form-control-sm" placeholder="Password admin">
                    </div>
                </div>

                <div id="redeem-box" class="redeem-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0" for="redeem_points">Redeem Poin</label>
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="setMaxRedeem()">Gunakan Maksimal</button>
                    </div>
                    <div class="input-group input-group-sm">
                        <input type="number" name="redeem_points" id="redeem_points" class="form-control"
                               value="0" min="0" step="1" oninput="recalcCart(); savePosDraft();">
                        <span class="input-group-text">poin</span>
                    </div>
                    <div id="redeem-info" class="form-text" style="font-size:.74rem"></div>
                    <div id="redeem-warning" style="display:none;font-size:.74rem;color:#dc2626;margin-top:4px"></div>
                </div>

                <div class="d-flex justify-content-between mb-1" style="font-size:.84rem;display:none!important" id="redeem-row">
                    <span style="color:#6b7280">Potongan Poin (<span id="redeem-points-label">0</span> poin)</span>
                    <span id="summary-redeem" style="font-weight:600;color:#dc2626">-Rp 0</span>
                </div>
                <hr style="margin:10px 0">
                <div class="d-flex justify-content-between mb-3" style="font-size:1.1rem;font-weight:800">
                    <span>Total</span>
                    <span id="summary-total" style="color:var(--primary-dark)">Rp 0</span>
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Catatan transaksi..." oninput="savePosDraft()"></textarea>
                </div>

                <button type="button" onclick="showPostponedDrafts()" id="btn-show-drafts"
                        class="btn btn-outline-primary w-100 py-2 mb-2"
                        style="font-size:.9rem;font-weight:600;border-radius:10px;border-width:2px;">
                    <i class="bi bi-card-list me-2"></i>Daftar Draft Tersimpan
                </button>

                <button type="button" onclick="postponeTransaction()" id="btn-postpone"
                        class="btn btn-warning w-100 py-2 mb-2"
                        style="font-size:.9rem;font-weight:600;border-radius:10px;color:#92400e;background:#fde047;border:none">
                    <i class="bi bi-pause-circle me-2"></i>Simpan ke Draft (Postpone)
                </button>

                <button type="button" onclick="openPaymentModal()" id="btn-checkout"
                        class="btn btn-primary w-100 py-3"
                        style="font-size:1rem;font-weight:700;border-radius:10px">
                    <i class="bi bi-credit-card me-2"></i>Proses Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Wadah Data Tersembunyi Keranjang -->
<div id="cart-data"></div>

<!-- Modal Pembayaran (Overlay) -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="paymentModalLabel"><i class="bi bi-wallet2 me-2"></i>Penyelesaian Transaksi</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        
        <div class="text-center mb-4">
            <div style="font-size:.9rem;color:#6b7280">Total Tagihan</div>
            <div id="modal-total-display" style="font-size:2rem;font-weight:800;color:var(--primary-dark)">Rp 0</div>
        </div>

        <div class="mb-4">
            <label class="form-label" style="font-weight:600">Uang Diterima dari Pelanggan <span class="text-danger">*</span></label>
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-light">Rp</span>
                <input type="text" inputmode="numeric" id="cash_received_display" class="form-control form-control-lg"
                       placeholder="0" oninput="formatCashInput(this)" style="font-weight:700;font-size:1.5rem" autocomplete="off" required>
                <input type="hidden" name="cash_received" id="cash_received" value="">
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center">
                @foreach([10000,20000,50000,100000,200000,500000] as $nom)
                <button type="button" class="btn btn-outline-secondary" style="font-weight:600"
                        onclick="setCash({{ $nom }})">{{ number_format($nom/1000) }}rb</button>
                @endforeach
                <button type="button" class="btn btn-outline-primary" style="font-weight:600" onclick="setExact()">Uang Pas</button>
            </div>
        </div>

        <div class="mb-3 p-3 text-center" style="background:#f0fdf4;border-radius:12px;border:1px solid #bbf7d0">
            <div style="font-size:.85rem;color:#166534;font-weight:600">Kembalian</div>
            <div id="change-amount" style="font-size:1.8rem;font-weight:800;color:#16a34a">Rp 0</div>
        </div>

        <button type="button" onclick="submitPos()" class="btn btn-primary w-100 py-3 mt-2" style="font-size:1.1rem;font-weight:700;border-radius:10px">
            <i class="bi bi-check-circle me-2"></i>Konfirmasi & Simpan Transaksi
        </button>
      </div>
    </div>
  </div>
</div>
</form>

<!-- Modal Daftar Draft -->
<div class="modal fade" id="draftModal" tabindex="-1" aria-labelledby="draftModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="draftModalLabel"><i class="bi bi-card-list me-2" style="color:var(--primary)"></i>Draft Transaksi Tersimpan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.85rem">
                <thead class="table-light">
                    <tr>
                        <th>Waktu Disimpan</th>
                        <th>Pelanggan</th>
                        <th>Total Item</th>
                        <th>Subtotal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="draft-table-body">
                    <!-- Data draft di-render lewat JS -->
                </tbody>
            </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const POS_DRAFT_KEY = 'pos_tani_draft_v2';
const POS_POSTPONED_KEY = 'pos_tani_postponed_v1';
const currentUserRole = @json(auth()->user()->role);
const canUnderHppWithoutApproval = ['pemilik','admin'].includes(currentUserRole);
const newCustomerFromKasir = @json($newCustomer);
const redeemRule = {
    pointValue: {{ (float) ($rule->redeem_point_value ?? 100) }},
    minimumPoints: {{ (float) ($rule->minimum_redeem_points ?? 100) }},
    maxPercent: {{ (float) ($rule->max_redeem_percent ?? 50) }},
    multiple: {{ (int) ($rule->redeem_multiple ?? 100) }},
};
const tierDiscounts = {
    bronze: {{ (float) ($rule->discount_bronze ?? 0) }},
    silver: {{ (float) ($rule->discount_silver ?? 0) }},
    gold: {{ (float) ($rule->discount_gold ?? 0) }},
};
let cart = [];
let customer = null;
let memberDiscount = 0;
let paymentStepOpen = false;

let cartSortBy = null;
let cartSortDir = 'asc';

function sortCart(column) {
    if (cartSortBy === column) {
        cartSortDir = cartSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        cartSortBy = column;
        cartSortDir = 'asc';
    }

    cart.sort((a, b) => {
        let valA = a[column];
        let valB = b[column];

        if (typeof valA === 'string') valA = valA.toLowerCase();
        if (typeof valB === 'string') valB = valB.toLowerCase();

        if (valA < valB) return cartSortDir === 'asc' ? -1 : 1;
        if (valA > valB) return cartSortDir === 'asc' ? 1 : -1;
        return 0;
    });

    ['product_name', 'final_unit_price', 'qty', 'subtotal'].forEach(col => {
        const icon = document.getElementById('sort-icon-' + col);
        if (icon) {
            if (col === cartSortBy) {
                icon.innerHTML = cartSortDir === 'asc' ? '<i class="bi bi-arrow-up-short" style="font-size: 1.1em; color: var(--primary);"></i>' : '<i class="bi bi-arrow-down-short" style="font-size: 1.1em; color: var(--primary);"></i>';
            } else {
                icon.innerHTML = '<i class="bi bi-arrow-down-up text-muted" style="font-size:0.8em;opacity:0.4"></i>';
            }
        }
    });

    renderCart();
    savePosDraft();
}

function money(n){ return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }
function encodeObj(obj){ return encodeURIComponent(JSON.stringify(obj)); }
function decodeObj(str){ return JSON.parse(decodeURIComponent(str)); }
function lineSubtotal(item){ return Math.max(1, parseInt(item.qty,10)||1) * Math.max(0, parseFloat(item.final_unit_price)||0); }
function isBelowHpp(item){ return Number(item.hpp || 0) > 0 && Number(item.final_unit_price || 0) < Number(item.hpp || 0); }

function normalizeCartItem(item){
    return {
        product_id: Number(item.product_id || item.id),
        product_name: item.product_name || '',
        unit: item.unit || '',
        selling_price: Number(item.selling_price || 0),
        hpp: Number(item.hpp || 0),
        final_unit_price: Number(item.final_unit_price || item.selling_price || 0),
        discount_source: item.discount_source || 'none',
        promo: item.promo || null,
        promo_redeem_points: Number(item.promo_redeem_points || 0),
        promo_redeem_amount: Number(item.promo_redeem_amount || 0),
        qty: Math.max(1, parseInt(item.qty,10)||1),
        subtotal: 0,
        stock: Number(item.stock || 0),
    };
}

function syncCartSubtotals(){ cart.forEach(item => item.subtotal = lineSubtotal(item)); }
function syncCartHiddenInputs(){
    document.getElementById('cart-data').innerHTML = cart.map((item,i)=>`
        <input type="hidden" name="items[${i}][product_id]" value="${item.product_id}">
        <input type="hidden" name="items[${i}][qty]" value="${item.qty}">
        <input type="hidden" name="items[${i}][final_unit_price]" value="${item.final_unit_price}">
        <input type="hidden" name="items[${i}][promo_redeem_points]" value="${item.promo_redeem_points || 0}">
        <input type="hidden" name="items[${i}][promo_redeem_amount]" value="${item.promo_redeem_amount || 0}">
    `).join('');
}

function savePosDraft(){
    // Auto-save dimatikan agar draft tidak tersambung ke akun lain jika lupa logout.
    // Draft hanya tersimpan bila menekan tombol "Simpan ke Draft" (Tunda Transaksi).
}
function clearPosDraft(){ try { localStorage.removeItem(POS_DRAFT_KEY); } catch(e) {} }
function restorePosDraft(){
    try {
        const raw = localStorage.getItem(POS_DRAFT_KEY);
        if(!raw) return;
        const draft = JSON.parse(raw);
        cart = Array.isArray(draft.cart) ? draft.cart.map(normalizeCartItem) : [];
        if(draft.customer) selectCustomer(draft.customer, false, false);
        
        const draftCash = draft.cash_received || '';
        document.getElementById('cash_received').value = draftCash;
        if(document.getElementById('cash_received_display')) {
            document.getElementById('cash_received_display').value = draftCash ? Number(draftCash).toLocaleString('id-ID') : '';
        }
        
        document.getElementById('redeem_points').value = draft.redeem_points || 0;
        document.getElementById('notes').value = draft.notes || '';
        renderCart(false);
    } catch(e) {}
}
function goRegisterMemberFromPos(event){
    event.preventDefault();
    savePosDraft();
    window.location.href = event.currentTarget.href;
}

// PENCARIAN PELANGGAN
let custTimer;
document.getElementById('customer-search').addEventListener('focus', function(){ searchCustomer(this.value.trim()); });
document.getElementById('customer-search').addEventListener('input', function(){
    clearTimeout(custTimer);
    const q = this.value.trim();
    custTimer = setTimeout(() => searchCustomer(q), 220);
});
function searchCustomer(q){
    fetch(`{{ route('customers.search') }}?q=${encodeURIComponent(q)}`, {headers:{'Accept':'application/json'}})
        .then(r=>r.json()).then(data=>{
        const el = document.getElementById('customer-results');
        if(!data.length){ el.innerHTML='<div class="product-item" style="color:#9ca3af">Member tidak ditemukan</div>'; el.style.display='block'; return; }
        el.innerHTML = data.map(c=>`
            <div class="product-item" data-customer="${encodeObj(c)}" onclick="selectCustomer(decodeObj(this.dataset.customer))">
                <div class="min-w-0">
                    <div style="font-weight:600">${c.full_name}</div>
                    <div class="muted-line"><i class="bi bi-whatsapp me-1"></i>${c.whatsapp_number||'-'}</div>
                    <div class="muted-line"><i class="bi bi-geo-alt me-1"></i>${c.address || 'Alamat belum diisi'}</div>
                </div>
                <span class="badge-tier badge-${c.tier}">${String(c.tier || 'bronze').toUpperCase()}</span>
            </div>`).join('');
        el.style.display='block';
    }).catch(()=>{});
}
async function selectCustomer(c, resetRedeem = true, refreshPricing = true){
    customer = c;
    document.getElementById('customer_id').value = c.id;
    document.getElementById('customer-search-wrap').style.display='none';
    document.getElementById('customer-display').style.display='block';
    document.getElementById('cust-name').textContent = c.full_name;
    document.getElementById('cust-points').textContent = Number(c.point_balance || 0).toLocaleString('id-ID');
    document.getElementById('cust-phone').textContent = c.whatsapp_number || '';
    document.getElementById('cust-discount').textContent = Number(tierDiscounts[c.tier] || 0).toLocaleString('id-ID');
    const addr = document.getElementById('cust-address');
    addr.textContent = c.address ? `Alamat: ${c.address}` : 'Alamat: belum diisi';
    addr.style.display = 'block';
    document.getElementById('redeem-box').style.display = 'block';
    if(resetRedeem) document.getElementById('redeem_points').value = 0;
    const tierColors = {gold:'badge-gold',silver:'badge-silver',bronze:'badge-bronze'};
    document.getElementById('cust-tier-badge').innerHTML = `<span class="badge-tier ${tierColors[c.tier]||''}">${String(c.tier || 'bronze').toUpperCase()}</span>`;
    document.getElementById('customer-results').style.display='none';

    if(c.whatsapp_number){
        const phone = c.whatsapp_number.replace(/^0/,'62');
        const msg = encodeURIComponent(`Halo ${c.full_name},\nBerikut informasi membership Anda di *UD. Tani Agung Ngawi*:\n\nTier     : ${String(c.tier || 'bronze').charAt(0).toUpperCase()+String(c.tier || 'bronze').slice(1)}\nSaldo Poin : ${Number(c.point_balance || 0).toLocaleString('id-ID')} poin\n\nTerima kasih telah menjadi pelanggan setia kami.`);
        document.getElementById('cust-wa-link').href = `https://wa.me/${phone}?text=${msg}`;
        document.getElementById('cust-wa-btn').style.display='inline';
    }

    if(refreshPricing && cart.length) await refreshCartPricingForCustomer();
    recalcCart();
    savePosDraft();
}
function clearCustomer(){
    customer = null;
    memberDiscount = 0;
    document.getElementById('customer_id').value='';
    document.getElementById('customer-search-wrap').style.display='block';
    document.getElementById('customer-display').style.display='none';
    document.getElementById('customer-search').value='';
    document.getElementById('cust-wa-btn').style.display='none';
    document.getElementById('redeem-box').style.display='none';
    document.getElementById('redeem_points').value = 0;
    refreshCartPricingForCustomer().finally(()=>{ recalcCart(); savePosDraft(); });
}

// PENCARIAN PRODUK
let prodTimer;
document.getElementById('product-search').addEventListener('focus', function(){ searchProduct(this.value.trim()); });
document.getElementById('product-search').addEventListener('input', function(){
    clearTimeout(prodTimer);
    const q = this.value.trim();
    prodTimer = setTimeout(() => searchProduct(q), 220);
});
function searchProduct(q){
    fetch(`{{ route('products.search') }}?q=${encodeURIComponent(q)}`, {headers:{'Accept':'application/json'}})
        .then(r=>r.json()).then(data=>{
        const el = document.getElementById('product-results');
        if(!data.length){ el.innerHTML='<div class="product-item" style="color:#9ca3af">Produk tidak ditemukan</div>'; el.style.display='block'; return; }
        el.innerHTML = data.map(p=>`
            <div class="product-item" data-product="${encodeObj(p)}" onclick="addToCart(decodeObj(this.dataset.product))">
                <div class="min-w-0">
                    <div style="font-weight:600;font-size:.86rem">${p.product_name}</div>
                    <div class="muted-line">${p.product_code} &bull; Stok: ${p.stock} ${p.unit}</div>
                    <div class="muted-line">HPP: ${money(p.hpp || 0)}</div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-weight:700;font-size:.86rem;color:var(--primary-dark)">${money(p.selling_price)}</div>
                </div>
            </div>`).join('');
        el.style.display='block';
    }).catch(()=>{});
}
async function getResolvedPricing(productId){
    const res = await fetch('{{ route("api.price-check") }}',{
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
        body: JSON.stringify({product_id: productId, customer_id: customer?.id||null})
    });
    if(!res.ok){
        const err = await res.json().catch(()=>({message:'Gagal mengambil harga produk.'}));
        throw new Error(err.message || 'Gagal mengambil harga produk.');
    }
    return await res.json();
}
async function addToCart(product){
    document.getElementById('product-results').style.display='none';
    document.getElementById('product-search').value='';
    try {
        const pricing = await getResolvedPricing(product.id);
        const existing = cart.find(i=>Number(i.product_id)===Number(product.id));
        if(existing){
            existing.qty = Math.max(1, Number(existing.qty || 1)) + 1;
            if(!existing.hpp) existing.hpp = Number(pricing.hpp || product.hpp || 0);
        } else {
            cart.push(normalizeCartItem({
                product_id: product.id,
                product_name: product.product_name,
                unit: product.unit,
                selling_price: Number(pricing.selling_price ?? product.selling_price ?? 0),
                hpp: Number(pricing.hpp ?? product.hpp ?? 0),
                final_unit_price: Number(pricing.final_price ?? product.selling_price ?? 0),
                discount_source: pricing.discount_source || 'none',
                promo: pricing.promo,
                promo_redeem_points: Number(pricing.promo_redeem_points || 0),
                promo_redeem_amount: Number(pricing.promo_redeem_amount || 0),
                qty: 1,
                stock: product.stock,
            }));
        }
        renderCart();
    } catch(e) {
        Swal.fire({icon:'error', title:'Gagal', text: e.message || 'Produk gagal ditambahkan ke keranjang.'});
    }
}
async function refreshCartPricingForCustomer(){
    for(const item of cart){
        if(item.discount_source === 'nego') continue;
        try {
            const pricing = await getResolvedPricing(item.product_id);
            item.selling_price = Number(pricing.selling_price || item.selling_price || 0);
            item.hpp = Number(pricing.hpp || item.hpp || 0);
            item.final_unit_price = Number(pricing.final_price || item.selling_price || 0);
            item.discount_source = pricing.discount_source || 'none';
            item.promo = pricing.promo;
            item.promo_redeem_points = Number(pricing.promo_redeem_points || 0);
            item.promo_redeem_amount = Number(pricing.promo_redeem_amount || 0);
        } catch(e) {}
    }
    renderCart();
}

function removeFromCart(idx, event){
    if(event){ event.preventDefault(); event.stopPropagation(); }
    cart = cart.filter((_,i)=>i!==idx);
    renderCart();
    savePosDraft();
}
function updateQty(idx, val){
    if(!cart[idx]) return;
    const qty = parseInt(val,10);
    if(isNaN(qty) || qty < 1) return;
    cart[idx].qty = qty;
    renderCart();
    savePosDraft();
}
function setNegoPrice(idx, val){
    if(!cart[idx]) return;
    if(String(val).trim()==='') return;
    const price = parseFloat(String(val).replace(/\./g, ''));
    if(isNaN(price) || price < 0) return;
    cart[idx].final_unit_price = price;
    cart[idx].discount_source = 'nego';
    renderCart();
    savePosDraft();
}
function renderCart(saveDraft = true){
    const tbody = document.getElementById('cart-body');
    syncCartSubtotals();
    if(cart.length===0){
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4" style="color:#9ca3af"><i class="bi bi-cart" style="font-size:2rem;display:block;margin-bottom:6px"></i>Belum ada produk dipilih</td></tr>`;
        syncCartHiddenInputs();
        document.getElementById('cart-count').textContent='0 item';
        updateSummary(0,0,0,false);
        updateUnderHppBox();
        if(saveDraft) savePosDraft();
        return;
    }

    let hasPromo = false;
    tbody.innerHTML = cart.map((item,i)=>{
        let priceBadge = '';
        if(item.discount_source==='promo' || item.discount_source==='promo+member'){
            hasPromo = true;
            if(item.promo) {
                priceBadge += `<span class="promo-badge"><i class="bi bi-tag-fill"></i> Promo s/d ${item.promo.end_date}</span>${item.promo_redeem_points ? `<span class="promo-badge" style="background:#fffbeb;color:#92400e"><i class="bi bi-star-fill"></i> Redeem promo ${item.promo_redeem_points} poin</span>` : ''}`;
            }
        }
        if(item.discount_source==='member' || item.discount_source==='promo+member') {
            priceBadge += `<span class="member-badge"><i class="bi bi-award"></i> Diskon Member</span>`;
        }
        const under = isBelowHpp(item);
        const underBadge = under ? `<div class="hpp-warning-badge"><i class="bi bi-exclamation-triangle"></i> Di bawah HPP ${money(item.hpp)}</div>` : `<div style="font-size:.7rem;color:#9ca3af;margin-top:4px">HPP: ${money(item.hpp || 0)}</div>`;
        return `<tr>
            <td>
                <div style="font-weight:600;font-size:.84rem">${item.product_name}</div>
                <div style="font-size:.72rem;color:#059669;margin-top:2px;font-weight:600"><i class="bi bi-box-seam me-1"></i>Sisa Stok: ${item.stock} ${item.unit}</div>
                <div class="d-flex flex-wrap gap-1 mt-1">${priceBadge}</div>
                ${underBadge}
            </td>
            <td>
                ${currentUserRole !== 'pemilik'
                    ? `<div style="font-weight:600; font-size: 1rem;">${money(item.final_unit_price)}</div>
                       <input type="hidden" value="${item.final_unit_price}" onchange="setNegoPrice(${i},this.value)">`
                    : `<input type="text" class="form-control form-control-sm rupiah-input ${under?'is-invalid':''}" style="width:120px"
                       value="${Number(item.final_unit_price).toLocaleString('id-ID')}" 
                       onchange="setNegoPrice(${i},this.value)" onkeyup="if(event.key==='Enter') this.blur()">`
                }
                ${item.selling_price !== item.final_unit_price ? `<div style="font-size:.7rem;color:#9ca3af;text-decoration:line-through">${money(item.selling_price)}</div>` : ''}
                ${under ? `<div class="price-warning">${canUnderHppWithoutApproval ? 'Boleh diproses oleh admin/pemilik, namun akan masuk log.' : 'Butuh otorisasi admin/pemilik sebelum checkout.'}</div>` : ''}
            </td>
            <td><input type="number" class="form-control form-control-sm" style="width:70px" value="${item.qty}" min="1" onchange="updateQty(${i},this.value)" onkeyup="if(event.key==='Enter') this.blur()"></td>
            <td style="font-weight:700;white-space:nowrap">${money(item.subtotal)}</td>
            <td><button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="removeFromCart(${i}, event)"><i class="bi bi-trash"></i></button></td>
        </tr>`;
    }).join('');

    syncCartHiddenInputs();
    document.getElementById('cart-count').textContent = `${cart.length} item`;
    recalcCart(hasPromo);
    updateUnderHppBox();
    if(saveDraft) savePosDraft();
}
function updateUnderHppBox(){
    const below = cart.filter(isBelowHpp);
    const box = document.getElementById('under-hpp-box');
    const msg = document.getElementById('under-hpp-message');
    const fields = document.getElementById('under-hpp-auth-fields');
    if(!below.length){ box.style.display='none'; fields.style.display='none'; return; }
    box.style.display='block';
    const names = below.map(i=>`${i.product_name} (${money(i.final_unit_price)} < HPP ${money(i.hpp)})`).join(', ');
    if(canUnderHppWithoutApproval){
        msg.textContent = `Item berikut memakai harga di bawah HPP: ${names}. Transaksi boleh diproses, tetapi sistem akan mencatat log peringatan.`;
        fields.style.display = 'none';
    } else {
        msg.textContent = `Item berikut memakai harga di bawah HPP: ${names}. Kasir harus meminta otorisasi admin/pemilik sebelum transaksi dapat diproses.`;
        fields.style.display = 'block';
    }
}
function recalcCart(hasPromo=false){
    syncCartSubtotals();
    const subtotal = cart.reduce((s,i)=>s+i.subtotal,0);
    const anyPromo = cart.some(i=>i.discount_source==='promo' || i.discount_source==='promo+member') || hasPromo;

    // Hitung diskon member berdasarkan tier pelanggan
    // Diskon member bisa ditumpuk dengan promo
    let discPct = 0, discAmt = 0;
    if(customer && customer.tier){
        discPct = Number(tierDiscounts[customer.tier] || 0);
        // Hitung dari semua item
        const baseForMember = cart.reduce((s,i) => s + i.subtotal, 0);
        discAmt = Math.round(baseForMember * discPct / 100);
    }

    document.getElementById('discount_percent').value = discPct;
    updateSummary(subtotal, discAmt, discPct, anyPromo);
}
function maxRedeemPointsFor(totalBeforeRedeem){
    if(!customer || totalBeforeRedeem <= 0 || redeemRule.pointValue <= 0) return 0;
    const balance = Math.floor(Number(customer.point_balance)||0);
    const maxAmountByPercent = Math.floor(totalBeforeRedeem * (redeemRule.maxPercent / 100));
    const maxAmount = Math.min(totalBeforeRedeem, maxAmountByPercent);
    const maxP = Math.max(0, Math.min(balance, Math.floor(maxAmount / redeemRule.pointValue)));
    return Math.floor(maxP / redeemRule.multiple) * redeemRule.multiple;
}
function calculateRedeem(totalBeforeRedeem){
    const input = document.getElementById('redeem_points');
    const warning = document.getElementById('redeem-warning');
    const info = document.getElementById('redeem-info');
    const requested = Math.floor(Number(input?.value || 0));
    let points = 0, amount = 0, message = '';
    if(!customer){
        if(info) info.textContent = 'Pilih member terlebih dahulu untuk menggunakan redeem poin.';
        if(warning) warning.style.display = 'none';
        return {points, amount};
    }
    const balance = Math.floor(Number(customer.point_balance)||0);
    const maxPoints = maxRedeemPointsFor(totalBeforeRedeem);
    if(info) info.textContent = `Saldo ${balance.toLocaleString('id-ID')} poin • 1 poin = ${money(redeemRule.pointValue)} • Min ${Number(redeemRule.minimumPoints).toLocaleString('id-ID')} poin • Maks ${Number(redeemRule.maxPercent).toLocaleString('id-ID')}% transaksi / ${maxPoints.toLocaleString('id-ID')} poin`;
    if(requested > 0){
        if(requested % redeemRule.multiple !== 0) message = `Poin yang digunakan harus kelipatan ${redeemRule.multiple}.`;
        else if(requested < redeemRule.minimumPoints) message = `Minimal redeem ${Number(redeemRule.minimumPoints).toLocaleString('id-ID')} poin.`;
        else if(requested > balance) message = `Saldo poin tidak cukup. Saldo tersedia ${balance.toLocaleString('id-ID')} poin.`;
        else if(requested > maxPoints) message = `Maksimal redeem transaksi ini ${maxPoints.toLocaleString('id-ID')} poin.`;
        else { points = requested; amount = Math.min(totalBeforeRedeem, points * redeemRule.pointValue); }
    }
    if(warning){ warning.textContent = message; warning.style.display = message ? 'block' : 'none'; }
    return {points, amount};
}
function updateSummary(subtotal, discAmt, discPct, hasPromo=false){
    const totalBeforeRedeem = Math.max(0, subtotal - discAmt);
    const redeem = calculateRedeem(totalBeforeRedeem);
    const total = Math.max(0, totalBeforeRedeem - redeem.amount);
    document.getElementById('summary-subtotal').textContent = money(subtotal);
    document.getElementById('disc-pct').textContent = discPct;
    document.getElementById('summary-discount').textContent = '-' + money(discAmt);
    document.getElementById('discount-row').style.display = discAmt>0 ? 'flex' : 'none';
    document.getElementById('redeem-points-label').textContent = redeem.points.toLocaleString('id-ID');
    document.getElementById('summary-redeem').textContent = '-' + money(redeem.amount);
    document.getElementById('redeem-row').style.cssText = redeem.amount>0 ? 'font-size:.84rem;display:flex!important' : 'font-size:.84rem;display:none!important';
    document.getElementById('summary-total').textContent = money(total);
    document.getElementById('promo-note').style.display = hasPromo ? 'block' : 'none';
    calcChange();
}
function setMaxRedeem(){
    if(!customer){ Swal.fire({icon:'warning', title:'Perhatian', text:'Pilih member terlebih dahulu.'}); return; }
    syncCartSubtotals();
    const subtotal = cart.reduce((s,i)=>s+i.subtotal,0);
    let discPct = 0, discAmt = 0;
    if(customer && customer.tier){
        discPct = Number(tierDiscounts[customer.tier] || 0);
        const baseForMember = cart.reduce((s,i) => s + i.subtotal, 0);
        discAmt = Math.round(baseForMember * discPct / 100);
    }
    const totalBeforeRedeem = Math.max(0, subtotal - discAmt);
    const maxPoints = maxRedeemPointsFor(totalBeforeRedeem);
    if(maxPoints <= 0){ Swal.fire({icon:'warning', title:'Perhatian', text:'Poin belum bisa digunakan untuk transaksi ini.'}); return; }
    document.getElementById('redeem_points').value = maxPoints;
    recalcCart(); savePosDraft();
}
function calcChange(){
    const totalText = document.getElementById('summary-total').textContent.replace(/[^0-9]/g,'');
    const total = parseInt(totalText)||0;
    const cash  = parseInt(document.getElementById('cash_received').value)||0;
    const change = Math.max(0, cash-total);
    document.getElementById('change-amount').textContent = money(change);
    document.getElementById('change-amount').style.color = cash < total ? '#dc2626' : 'var(--primary-dark)';
}

function formatCashInput(el) {
    let val = el.value.replace(/[^0-9]/g, '');
    if(!val) {
        el.value = '';
        document.getElementById('cash_received').value = '';
    } else {
        el.value = Number(val).toLocaleString('id-ID');
        document.getElementById('cash_received').value = val;
    }
    calcChange();
    savePosDraft();
}

function setCash(val){ 
    document.getElementById('cash_received').value = val;
    if(document.getElementById('cash_received_display')){
        document.getElementById('cash_received_display').value = Number(val).toLocaleString('id-ID');
    }
    calcChange(); 
    savePosDraft(); 
}

function setExact(){
    const totalText = document.getElementById('summary-total').textContent.replace(/[^0-9]/g,'');
    const total = parseInt(totalText)||0;
    document.getElementById('cash_received').value = total;
    if(document.getElementById('cash_received_display')){
        document.getElementById('cash_received_display').value = total > 0 ? Number(total).toLocaleString('id-ID') : '';
    }
    calcChange(); 
    savePosDraft();
}
let paymentModalInstance = null;
async function openPaymentModal(){
    if(cart.length===0){ Swal.fire({icon:'warning', title:'Perhatian', text:'Keranjang masih kosong.'}); return; }
    syncCartSubtotals();

    // Pastikan input selalu direset ketika akan melakukan pembayaran
    document.getElementById('cash_received').value = '';
    if(document.getElementById('cash_received_display')) {
        document.getElementById('cash_received_display').value = '';
    }
    
    // Validasi sebelum buka modal
    const below = cart.filter(isBelowHpp);
    if(below.length){
        if(canUnderHppWithoutApproval){
            const result = await Swal.fire({
                title: 'Harga di bawah HPP',
                text: 'Ada item dengan harga di bawah HPP. Transaksi tetap diproses dan masuk log sistem?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Proses',
                cancelButtonText: 'Batal'
            });
            if(!result.isConfirmed) return;
        } else {
            if(!document.getElementById('under_hpp_admin_email').value || !document.getElementById('under_hpp_admin_password').value){
                Swal.fire({icon:'error', title:'Otorisasi Diperlukan', text:'Harga di bawah HPP membutuhkan otorisasi admin/pemilik. Isi username/email dan password admin terlebih dahulu di sebelah kiri layar.'});
                return;
            }
        }
    }
    
    const redeemWarning = document.getElementById('redeem-warning');
    if(redeemWarning && redeemWarning.style.display !== 'none' && document.getElementById('redeem_points').value > 0){
        Swal.fire({icon:'warning', title:'Perhatian', text:redeemWarning.textContent});
        return;
    }

    // Tampilkan total di modal
    document.getElementById('modal-total-display').textContent = document.getElementById('summary-total').textContent;
    calcChange();

    if(!paymentModalInstance) paymentModalInstance = new bootstrap.Modal(document.getElementById('paymentModal'));
    paymentModalInstance.show();

    setTimeout(() => {
        const displayInput = document.getElementById('cash_received_display');
        if(displayInput) {
            displayInput.focus();
            // Pindahkan kursor ke ujung teks
            const len = displayInput.value.length;
            displayInput.setSelectionRange(len, len);
        }
    }, 500);
}

function submitPos(){
    const totalText = document.getElementById('summary-total').textContent.replace(/[^0-9]/g,'');
    const total = parseInt(totalText)||0;
    const cash  = parseInt(document.getElementById('cash_received').value)||0;
    if(cash < total){ Swal.fire({icon:'error', title:'Pembayaran Kurang', text:'Uang diterima kurang dari total belanja.'}); return; }
    
    savePosDraft();
    syncCartHiddenInputs();
    
    // Tampilkan state loading
    const btnSubmit = document.querySelector('#paymentModal .btn-primary');
    if(btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
    }
    
    isSubmitting = true;
    document.getElementById('pos-form').submit();
}

// --- LOGIKA PENUNDAAN (DRAFT) TRANSAKSI ---
function postponeTransaction() {
    if (cart.length === 0) {
        Swal.fire({icon:'warning', title:'Perhatian', text:'Keranjang masih kosong, tidak ada yang perlu disimpan ke draft.'});
        return;
    }
    const notes = document.getElementById('notes')?.value || '';
    
    // Siapkan object draft
    const draftData = {
        id: Date.now(),
        savedAt: new Date().toLocaleString('id-ID'),
        cart: cart,
        customer: customer,
        notes: notes,
        subtotal: cart.reduce((s,i) => s + i.subtotal, 0)
    };

    // Ambil array postponed lama
    let postponed = [];
    try {
        const raw = localStorage.getItem(POS_POSTPONED_KEY);
        if (raw) postponed = JSON.parse(raw);
    } catch(e) {}
    
    // Tambahkan ke array
    postponed.push(draftData);
    localStorage.setItem(POS_POSTPONED_KEY, JSON.stringify(postponed));

    // Bersihkan layar kasir (reset semua)
    cart = [];
    clearCustomer();
    document.getElementById('cash_received').value = '';
    if(document.getElementById('cash_received_display')) document.getElementById('cash_received_display').value = '';
    document.getElementById('redeem_points').value = 0;
    document.getElementById('notes').value = '';
    renderCart(true);

    fetch('{{ route("kasir.log-draft") }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF},
        body: JSON.stringify({ action: 'Simpan Draft', detail: `Menyimpan draft transaksi atas nama ${draftData.customer ? draftData.customer.full_name : 'Umum / Non-member'} dengan subtotal ${money(draftData.subtotal)}` })
    }).catch(()=>{});

    Swal.fire({icon:'success', title:'Berhasil', text:'Transaksi berhasil disimpan ke Draft!', timer: 2000, showConfirmButton: false});
}

function emptyCart() {
    if (cart.length === 0) {
        Swal.fire({icon:'info', title:'Keranjang Kosong', text:'Keranjang sudah kosong.', timer: 1500, showConfirmButton:false});
        return;
    }
    Swal.fire({
        title: 'Kosongkan Keranjang?',
        text: 'Semua barang di keranjang akan dihapus beserta data pelanggan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Kosongkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            cart = [];
            clearCustomer();
            document.getElementById('notes').value = '';
            document.getElementById('cash_received').value = '';
            if(document.getElementById('cash_received_display')) document.getElementById('cash_received_display').value = '';
            localStorage.removeItem(POS_DRAFT_KEY);
            renderCart(false);
            Swal.fire({icon:'success', title:'Dikosongkan', text:'Keranjang telah dikosongkan.', timer: 1500, showConfirmButton: false});
        }
    });
}

let draftModalInstance = null;
function showPostponedDrafts() {
    if(!draftModalInstance) draftModalInstance = new bootstrap.Modal(document.getElementById('draftModal'));
    renderDraftsModal();
    draftModalInstance.show();
}

function renderDraftsModal() {
    let postponed = [];
    try {
        const raw = localStorage.getItem(POS_POSTPONED_KEY);
        if (raw) postponed = JSON.parse(raw);
    } catch(e) {}

    const tbody = document.getElementById('draft-table-body');
    if (postponed.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4" style="color:#9ca3af"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Belum ada draft transaksi yang disimpan.</td></tr>`;
        return;
    }

    tbody.innerHTML = postponed.map((d, index) => {
        const custName = d.customer ? d.customer.full_name : 'Umum / Non-member';
        const totalQty = d.cart.reduce((s,i) => s + Number(i.qty), 0);
        return `
        <tr>
            <td>${d.savedAt}</td>
            <td style="font-weight:600">${custName}</td>
            <td>${totalQty} barang</td>
            <td style="font-weight:700;color:var(--primary-dark)">${money(d.subtotal)}</td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-success me-1" onclick="loadPostponedDraft(${index})" title="Muat Draft">
                    <i class="bi bi-box-arrow-in-down"></i> Load
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePostponedDraft(${index})" title="Hapus Draft">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
}

function loadPostponedDraft(index) {
    if (cart.length > 0) {
        Swal.fire({
            title: 'Keranjang tidak kosong',
            text: 'Memuat draft akan menimpa keranjang Anda saat ini. Lanjutkan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Lanjutkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                executeLoadDraft(index);
            }
        });
        return;
    }
    executeLoadDraft(index);
}

function executeLoadDraft(index) {
    let postponed = [];
    try {
        const raw = localStorage.getItem(POS_POSTPONED_KEY);
        if (raw) postponed = JSON.parse(raw);
    } catch(e) {}

    const d = postponed[index];
    if (!d) return;

    // Load ke state
    cart = d.cart.map(normalizeCartItem);
    if (d.customer) {
        selectCustomer(d.customer, false, false);
    } else {
        clearCustomer();
    }
    document.getElementById('notes').value = d.notes || '';
    
    // Hapus dari list postponed karena sudah di-load
    postponed.splice(index, 1);
    localStorage.setItem(POS_POSTPONED_KEY, JSON.stringify(postponed));

    fetch('{{ route("kasir.log-draft") }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF},
        body: JSON.stringify({ action: 'Muat Draft', detail: `Memuat draft transaksi atas nama ${d.customer ? d.customer.full_name : 'Umum / Non-member'} dengan subtotal ${money(d.subtotal)}` })
    }).catch(()=>{});

    draftModalInstance.hide();
    renderCart(true);
}

function deletePostponedDraft(index) {
    Swal.fire({
        title: 'Hapus Draft?',
        text: 'Apakah Anda yakin ingin menghapus draft ini secara permanen?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            let postponed = [];
            try {
                const raw = localStorage.getItem(POS_POSTPONED_KEY);
                if (raw) postponed = JSON.parse(raw);
            } catch(e) {}
            
            const d = postponed[index];
            if (d) {
                fetch('{{ route("kasir.log-draft") }}', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF},
                    body: JSON.stringify({ action: 'Hapus Draft', detail: `Menghapus permanen draft atas nama ${d.customer ? d.customer.full_name : 'Umum / Non-member'}` })
                }).catch(()=>{});
            }

            postponed.splice(index, 1);
            localStorage.setItem(POS_POSTPONED_KEY, JSON.stringify(postponed));
            renderDraftsModal();
        }
    });
}
// --- AKHIR LOGIKA PENUNDAAN TRANSAKSI ---

document.addEventListener('click', e=>{
    if(!e.target.closest('#product-search') && !e.target.closest('#product-results')) document.getElementById('product-results').style.display='none';
    if(!e.target.closest('#customer-search') && !e.target.closest('#customer-results')) document.getElementById('customer-results').style.display='none';
});

const paymentModalEl = document.getElementById('paymentModal');
if(paymentModalEl) {
    paymentModalEl.addEventListener('hidden.bs.modal', function () {
        document.getElementById('cash_received').value = '';
        if(document.getElementById('cash_received_display')){
            document.getElementById('cash_received_display').value = '';
        }
        calcChange();
        savePosDraft();
    });
}

let isSubmitting = false;
let isNavigatingAway = false;
window.addEventListener('submit', function() { isSubmitting = true; });

document.addEventListener('click', function(e) {
    const link = e.target.closest('a');
    if (link && link.href && !link.target && !link.href.includes('javascript:') && !link.href.endsWith('#')) {
        // Abaikan jika menuju ke halaman yang sama (hanya hash beda) atau pathname sama
        if (link.pathname === window.location.pathname) {
            if (cart.length > 0) {
                e.preventDefault();
            }
            return;
        }
        
        if (cart.length > 0 && !isSubmitting) {
            e.preventDefault();
            Swal.fire({
                title: 'Keranjang Terisi',
                text: 'Ada barang di keranjang yang belum disimpan. Simpan ke draft sekarang?',
                icon: 'warning',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'Ya, Simpan ke Draft',
                denyButtonText: 'Tidak, Buang Saja',
                cancelButtonText: 'Batal Pindah'
            }).then((result) => {
                if (result.isConfirmed) {
                    postponeTransaction();
                    isNavigatingAway = true;
                    window.location.href = link.href;
                } else if (result.isDenied) {
                    cart = [];
                    localStorage.removeItem(POS_DRAFT_KEY);
                    isNavigatingAway = true;
                    window.location.href = link.href;
                }
            });
        }
    }
});

window.addEventListener('pageshow', function(e) {
    if(e.persisted || (window.performance && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
        if(!localStorage.getItem(POS_DRAFT_KEY)) {
            cart = [];
            clearCustomer();
            document.getElementById('notes').value = '';
            document.getElementById('cash_received').value = '';
            if(document.getElementById('cash_received_display')) document.getElementById('cash_received_display').value = '';
            renderCart(false);
            if(paymentModalInstance) paymentModalInstance.hide();
            const btnSubmit = document.querySelector('#paymentModal .btn-primary');
            if(btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-check-circle me-2"></i>Konfirmasi & Simpan Transaksi';
            }
        }
    }
});

(function initPosPage(){
    restorePosDraft();
    if(newCustomerFromKasir){
        selectCustomer(newCustomerFromKasir, true, true);
    }
    renderCart(false);
})();
</script>
@endpush
