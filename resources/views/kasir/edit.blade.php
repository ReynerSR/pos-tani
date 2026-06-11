@extends('layouts.app')
@section('title','Edit Nota')
@section('page_title','Edit Nota Transaksi')

@push('styles')
<style>
    .nota-item-card {
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:14px;
        padding:16px;
        margin-bottom:12px;
        box-shadow:0 2px 8px rgba(15,23,42,.03);
        transition:.15s;
    }
    .nota-item-card:hover { border-color:#bbf7d0; background:#fbfffd; }
    .nota-line-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
    .nota-line-title { font-size:.78rem; font-weight:800; color:#166534; text-transform:uppercase; letter-spacing:.45px; }
    .nota-line-grid {
        display:grid;
        grid-template-columns:1fr minmax(90px,.3fr) minmax(150px,.5fr) minmax(120px,.4fr);
        gap:14px;
        align-items:end;
    }
    .nota-line-grid > div { min-width:0; }
    .nota-line-grid .form-control,
    .nota-line-grid .input-group { min-width:0; }
    .total-box {
        min-height:40px;
        padding:8px 12px;
        border-radius:10px;
        background:#f8fafc;
        border:1px solid #e5e7eb;
        text-align:right;
        display:flex;
        flex-direction:column;
        justify-content:center;
    }
    .total-box .total-label { font-size:.72rem; color:#6b7280; line-height:1.1; }
    .total-box .line-total { font-size:1rem; font-weight:800; color:#111827; white-space:nowrap; line-height:1.25; }
    .remove-line-btn {
        background:#fff1f2;
        color:#dc2626;
        border:1px solid #fecdd3;
        border-radius:9px;
        width:36px;
        height:36px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
        transition:.15s;
        flex-shrink:0;
    }
    .remove-line-btn:hover { background:#ffe4e6; border-color:#fb7185; }
    .under-hpp-edit-box { display:none; background:#fff7ed; border:1px solid #fdba74; border-radius:10px; padding:10px 12px; margin-bottom:12px; }
    .under-hpp-edit-box .title { font-weight:800;color:#9a3412;font-size:.82rem; }
    .under-hpp-edit-box .desc { color:#9a3412;font-size:.75rem;line-height:1.35; }
    .price-warning { font-size:.72rem;color:#b45309;margin-top:4px; }

    /* Product search autocomplete */
    .prod-search-wrap { position:relative; }
    .prod-search-results {
        position:absolute; top:100%; left:0; right:0;
        background:#fff; border:1px solid #e5e7eb; border-top:none;
        border-radius:0 0 10px 10px; z-index:200;
        box-shadow:0 8px 24px rgba(0,0,0,.12);
        max-height:260px; overflow-y:auto; display:none;
    }
    .prod-search-item {
        padding:9px 13px; cursor:pointer;
        border-bottom:1px solid #f3f4f6;
        display:flex; justify-content:space-between; align-items:center; gap:10px;
        font-size:.82rem; transition:.12s;
    }
    .prod-search-item:hover { background:var(--primary-pale,#ecfdf5); }
    .prod-search-item:last-child { border-bottom:none; }
    .prod-search-item .pi-name { font-weight:600; }
    .prod-search-item .pi-meta { font-size:.72rem; color:#6b7280; margin-top:1px; }
    .prod-search-item .pi-price { font-weight:700; color:var(--primary-dark,#166534); white-space:nowrap; }

    .member-discount-row { display:none; }

    @media (max-width: 900px) {
        .nota-line-grid { grid-template-columns:1fr 1fr; }
        .nota-line-grid .total-box { grid-column:1 / -1; }
    }
    @media (max-width: 576px) {
        .nota-line-grid { grid-template-columns:1fr; }
        .total-box { text-align:left; }
    }
</style>
@endpush

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-pencil-square me-2" style="color:var(--primary)"></i>Edit Nota</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('kasir.history') }}">Riwayat</a></li><li class="breadcrumb-item active">{{ $transaction->transaction_number }}</li></ol></nav>
    </div>
    <a href="{{ request('back_url') ?: route('kasir.show', $transaction) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
</div>

<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Ketika nota direvisi, sistem akan mengembalikan stok dan poin transaksi lama terlebih dahulu, lalu menghitung ulang stok, total, dan poin sesuai data baru.</div>

<form method="POST" action="{{ route('kasir.update',$transaction) }}">
@csrf @method('PUT')
<input type="hidden" name="discount_percent" id="edit_discount_percent" value="{{ old('discount_percent', $transaction->discount_percent) }}">
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h6>Header Nota</h6></div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tanggal</label>
                    <input type="datetime-local" name="transaction_date" class="form-control" value="{{ old('transaction_date',$transaction->transaction_date->format('Y-m-d\TH:i')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Member</label>
                    <div id="customer-display" style="display:none; background:var(--primary-pale,#ecfdf5); border-radius:8px; border:1px solid #d1fae5; padding:10px; margin-bottom:4px;">
                        <div style="font-weight:600;font-size:.85rem" id="cust-name"></div>
                        <div style="font-size:.75rem;color:#374151;margin-bottom:8px" id="cust-phone"></div>
                        <div class="d-flex gap-2">
                            <button type="button" onclick="clearCustomer()" class="btn btn-sm btn-outline-secondary w-100" style="font-size:0.75rem"><i class="bi bi-pencil me-1"></i>Ubah Member</button>
                            <button type="button" onclick="setNonMember()" class="btn btn-sm btn-outline-danger" title="Jadikan Non-Member" style="font-size:0.75rem"><i class="bi bi-x-circle"></i></button>
                        </div>
                    </div>
                    <div id="customer-search-wrap" style="position:relative">
                        <div class="input-group input-group-sm mb-1">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="customer-search" class="form-control" placeholder="Cari member (nama/nomor)..." autocomplete="off">
                        </div>
                        <div style="font-size:.75rem; text-align:right;">
                            <a href="#" onclick="cancelSearch(event)" id="cancel-search-btn" style="display:none; color:#6b7280; text-decoration:none;"><i class="bi bi-arrow-left me-1"></i>Batal ubah</a>
                        </div>
                        <div id="customer-results" class="prod-search-results" style="max-height:200px"></div>
                    </div>
                    <input type="hidden" name="customer_id" id="edit_customer_id" value="{{ old('customer_id',$transaction->customer_id) }}">
                    <div class="form-text" id="edit_member_info" style="color:var(--primary,#166534)"></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Uang Diterima</label>
                    <input type="text" name="cash_received" id="edit_cash_received" class="form-control rupiah-input" value="{{ old('cash_received',(int)$transaction->cash_received) }}" oninput="calc()" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Redeem Poin</label>
                    <div class="input-group">
                        <input type="number" name="redeem_points" id="edit_redeem_points" class="form-control" value="{{ old('redeem_points',$transaction->points_redeemed ?? 0) }}" min="0" step="1" oninput="calc()">
                        <span class="input-group-text">poin</span>
                    </div>
                    <div class="form-text" id="edit_redeem_info">Redeem hanya aktif jika nota memakai member.</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes',$transaction->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6>Item Nota</h6>
                <div class="d-flex gap-2 align-items-center">
                    {{-- Product Search Bar --}}
                    <div class="prod-search-wrap" style="width:280px">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="edit_product_search" class="form-control"
                                   placeholder="Cari & tambah produk..." autocomplete="off">
                        </div>
                        <div id="edit_product_results" class="prod-search-results"></div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="itemsContainer"></div>
                <button type="button" class="btn btn-outline-secondary w-100 mt-2"
                        onclick="openProductSearch()"
                        style="border-style:dashed">
                    <i class="bi bi-plus-circle me-1"></i>Cari & Tambah Produk
                </button>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card" style="position:sticky; top:76px">
            <div class="card-header"><h6>Ringkasan</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span>Total Qty</span><strong id="summaryQty">0</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong id="summarySubtotal">Rp 0</strong></div>
                <div class="d-flex justify-content-between mb-2 member-discount-row" id="memberDiscountRow">
                    <span>Diskon Member (<span id="summaryDiscPct">0</span>%)</span>
                    <strong id="summaryDiscount" class="text-danger">-Rp 0</strong>
                </div>
                <div class="d-flex justify-content-between mb-2"><span>Potongan Poin</span><strong id="summaryRedeem" class="text-danger">-Rp 0</strong></div>
                <div class="d-flex justify-content-between mb-2 fw-bold"><span>Total Bayar</span><strong id="summaryTotal">Rp 0</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Kembalian</span><strong id="summaryChange">Rp 0</strong></div>
                <div id="editRedeemWarning" class="text-danger small mb-2" style="display:none"></div>
                <div id="editUnderHppBox" class="under-hpp-edit-box">
                    <div class="title"><i class="bi bi-exclamation-triangle me-1"></i>Harga di Bawah HPP</div>
                    <div id="editUnderHppMessage" class="desc mt-1"></div>
                    <div id="editUnderHppAuth" style="display:none" class="mt-2">
                        <label class="form-label mb-1" style="font-size:.76rem">Otorisasi Admin/Pemilik</label>
                        <input type="text" name="under_hpp_admin_email" id="edit_under_hpp_admin_email" class="form-control form-control-sm mb-2" placeholder="Username/email admin">
                        <input type="password" name="under_hpp_admin_password" id="edit_under_hpp_admin_password" class="form-control form-control-sm" placeholder="Password admin">
                    </div>
                </div>
                <hr>
                <button type="button" class="btn btn-primary w-100" onclick="validateEditBeforeSubmit()"><i class="bi bi-check-circle me-2"></i>Simpan Revisi Nota</button>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
@php
$editProducts = $products->map(function ($p) {
    return [
        'id'    => $p->id,
        'code'  => $p->product_code,
        'name'  => $p->product_name,
        'price' => (float) $p->selling_price,
        'hpp'   => (float) ($p->hpp ?? 0),
        'stock' => (int) $p->stock,
        'unit'  => $p->unit,
    ];
})->values();
$existingItems = $transaction->details->map(function ($d) {
    return [
        'product_id'       => $d->product_id,
        'qty'              => $d->qty,
        'final_unit_price' => (float) $d->final_unit_price,
    ];
})->values();
@endphp
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const currentUserRole = @json(auth()->user()->role);
const canUnderHppWithoutApproval = ['pemilik','admin'].includes(currentUserRole);
const products = @json($editProducts);
const existing = @json($existingItems);
const redeemRule = {
    pointValue:    {{ (float) ($rule->redeem_point_value ?? 100) }},
    minimumPoints: {{ (float) ($rule->minimum_redeem_points ?? 100) }},
    maxPercent:    {{ (float) ($rule->max_redeem_percent ?? 50) }},
};
const tierDiscounts = {
    bronze: {{ (float) ($rule->discount_bronze ?? 0) }},
    silver: {{ (float) ($rule->discount_silver ?? 0) }},
    gold:   {{ (float) ($rule->discount_gold ?? 0) }},
};

// ----------------------------------------------------------------
// Cart state: array of { product_id, qty, final_unit_price, hpp, ... }
// ----------------------------------------------------------------
let editCart = [];
let rowIndex  = 0;

function money(n){ return 'Rp ' + Number(n||0).toLocaleString('id-ID'); }

// ----------------------------------------------------------------
// Customer / tier helpers
// ----------------------------------------------------------------
const origCustomerId = {{ $transaction->customer_id ? $transaction->customer_id : 'null' }};
const origPointsRedeemed = {{ (float) ($transaction->points_redeemed ?? 0) }};
const origPointsEarned = {{ (float) ($transaction->points_earned ?? 0) }};

const initialCustomer = {!! $transaction->customer ? json_encode([
    'id' => $transaction->customer->id,
    'full_name' => $transaction->customer->full_name,
    'whatsapp_number' => $transaction->customer->whatsapp_number,
    'address' => $transaction->customer->address,
    'tier' => $transaction->customer->tier,
    'point_balance' => max(0, (float) $transaction->customer->point_balance + ((float) ($transaction->points_redeemed ?? 0) - (float) ($transaction->points_earned ?? 0))),
]) : 'null' !!};

let selectedCustomerObj = initialCustomer;

function getSelectedCustomer() {
    return selectedCustomerObj;
}
function getMemberDiscountPct() {
    const cust = getSelectedCustomer();
    if (!cust) return 0;
    return Number(tierDiscounts[cust.tier] || 0);
}

function onCustomerChange() {
    // Refresh harga semua item sesuai tier baru via API
    refreshAllPricesForCustomer().then(() => calc());
    updateMemberInfo();
}

function updateMemberInfo() {
    const cust = getSelectedCustomer();
    const el   = document.getElementById('edit_member_info');
    if (!cust) { 
        el.textContent = ''; 
        document.getElementById('customer-display').style.display = 'none';
        document.getElementById('customer-search-wrap').style.display = 'block';
        document.getElementById('cancel-search-btn').style.display = 'none';
        return; 
    }
    
    document.getElementById('customer-display').style.display = 'block';
    document.getElementById('customer-search-wrap').style.display = 'none';
    document.getElementById('cancel-search-btn').style.display = 'none';
    document.getElementById('cust-name').textContent = cust.full_name;
    document.getElementById('cust-phone').textContent = cust.whatsapp_number || '';
    
    const pct = getMemberDiscountPct();
    el.textContent = `Tier: ${cust.tier.toUpperCase()} • Diskon ${pct}% • Saldo ${Number(cust.point_balance).toLocaleString('id-ID')} poin`;
}

let tempCustomerObj = null;

function clearCustomer() {
    // Preserve the old customer object so we can cancel search
    tempCustomerObj = selectedCustomerObj;
    
    document.getElementById('customer-display').style.display = 'none';
    document.getElementById('customer-search-wrap').style.display = 'block';
    if (tempCustomerObj) {
        document.getElementById('cancel-search-btn').style.display = 'inline-block';
    }
    document.getElementById('customer-search').focus();
}

function cancelSearch(e) {
    if (e) e.preventDefault();
    document.getElementById('customer-search').value = '';
    document.getElementById('customer-results').style.display = 'none';
    selectedCustomerObj = tempCustomerObj;
    updateMemberInfo(); // restore view without changing server state/prices
}

function setNonMember() {
    Swal.fire({
        title: 'Ubah ke Umum?',
        text: 'Nota akan diubah menjadi Umum/Non-member. Yakin?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Ubah',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            selectedCustomerObj = null;
            tempCustomerObj = null;
            document.getElementById('edit_customer_id').value = '';
            document.getElementById('customer-search').value = '';
            onCustomerChange();
        }
    });
}

// ----------------------------------------------------------------
// Customer Search Autocomplete
// ----------------------------------------------------------------
let custTimer;
const custSearchInput   = document.getElementById('customer-search');
const custSearchResults = document.getElementById('customer-results');

custSearchInput.addEventListener('focus', () => doCustSearch(custSearchInput.value.trim()));
custSearchInput.addEventListener('input', () => {
    clearTimeout(custTimer);
    custTimer = setTimeout(() => doCustSearch(custSearchInput.value.trim()), 220);
});
document.addEventListener('click', (e) => {
    if (!custSearchInput.contains(e.target) && !custSearchResults.contains(e.target)) {
        custSearchResults.style.display = 'none';
    }
});

function doCustSearch(q) {
    if (!q) {
        custSearchResults.style.display = 'none';
        return;
    }
    fetch(`{{ route('customers.search') }}?q=${encodeURIComponent(q)}`, {headers:{'Accept':'application/json'}})
        .then(r=>r.json()).then(data=>{
        if(!data.length){ 
            custSearchResults.innerHTML='<div class="prod-search-item" style="color:#9ca3af">Member tidak ditemukan</div>'; 
            custSearchResults.style.display='block'; 
            return; 
        }
        custSearchResults.innerHTML = data.map(c=> {
            // Adjust points if this is the original transaction customer
            let displayPoints = Number(c.point_balance || 0);
            if (origCustomerId && String(c.id) === String(origCustomerId)) {
                displayPoints = Math.max(0, displayPoints + origPointsRedeemed - origPointsEarned);
            }
            c.point_balance = displayPoints;
            
            return `
            <div class="prod-search-item" onclick="selectCustomer(decodeObj(this.dataset.customer))" data-customer="${encodeURIComponent(JSON.stringify(c))}">
                <div>
                    <div class="pi-name">${c.full_name}</div>
                    <div class="pi-meta"><i class="bi bi-whatsapp me-1"></i>${c.whatsapp_number||'-'}</div>
                </div>
                <div style="text-align:right">
                    <span class="badge bg-${c.tier === 'gold' ? 'warning' : (c.tier === 'silver' ? 'secondary' : 'light text-dark')}">${String(c.tier || 'bronze').toUpperCase()}</span>
                </div>
            </div>`;
        }).join('');
        custSearchResults.style.display='block';
    }).catch(()=>{});
}

function selectCustomer(c) {
    custSearchResults.style.display = 'none';
    custSearchInput.value = '';
    selectedCustomerObj = c;
    document.getElementById('edit_customer_id').value = c.id;
    onCustomerChange();
}

function decodeObj(str){ return JSON.parse(decodeURIComponent(str)); }

// ----------------------------------------------------------------
// Price check via API (same as POS module)
// ----------------------------------------------------------------
async function fetchResolvedPrice(productId) {
    const cust = getSelectedCustomer();
    const res  = await fetch('{{ route("api.price-check") }}', {
        method:  'POST',
        headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
        body:    JSON.stringify({ product_id: productId, customer_id: cust?.id || null }),
    });
    if (!res.ok) throw new Error('Gagal mengambil harga produk.');
    return await res.json();
}

async function refreshAllPricesForCustomer() {
    for (const item of editCart) {
        try {
            const pricing = await fetchResolvedPrice(item.product_id);
            item.final_unit_price = Number(pricing.final_price || item.selling_price || 0);
            item.selling_price    = Number(pricing.selling_price || item.selling_price || 0);
        } catch(e) {}
    }
    renderEditCart();
}

// ----------------------------------------------------------------
// Cart operations
// ----------------------------------------------------------------
function findCartRow(productId) {
    return editCart.findIndex(i => String(i.product_id) === String(productId));
}

async function addProductToCart(product) {
    const existing = findCartRow(product.id);
    if (existing >= 0) {
        // Merge: increase qty
        editCart[existing].qty++;
        renderEditCart();
        calc();
        return;
    }
    // New item: resolve price
    try {
        const pricing = await fetchResolvedPrice(product.id);
        editCart.push({
            product_id:      product.id,
            product_name:    product.name,
            unit:            product.unit,
            selling_price:   Number(pricing.selling_price ?? product.price ?? 0),
            hpp:             Number(pricing.hpp ?? product.hpp ?? 0),
            final_unit_price: Number(pricing.final_price ?? product.price ?? 0),
            stock:           product.stock,
            qty:             1,
        });
        renderEditCart();
        calc();
    } catch(e) {
        Swal.fire({icon:'error', title:'Gagal', text: e.message || 'Gagal menambah produk.'});
    }
}

function removeCartItem(idx) {
    editCart.splice(idx, 1);
    renderEditCart();
    calc();
}

function updateCartQty(idx, val) {
    const qty = parseInt(val, 10);
    if (isNaN(qty) || qty < 1) return;
    editCart[idx].qty = qty;
    const row = document.getElementById(`editrow_${idx}`);
    if (row) {
        row.querySelector('.line-total').textContent = money(qty * editCart[idx].final_unit_price);
    }
    calc();
}

function updateCartPrice(idx, val) {
    const price = parseFloat(String(val).replace(/\./g, ''));
    if (isNaN(price) || price < 0) return;
    editCart[idx].final_unit_price = price;
    const row = document.getElementById(`editrow_${idx}`);
    if (row) {
        row.querySelector('.line-total').textContent = money(editCart[idx].qty * price);
    }
    calc();
}

// ----------------------------------------------------------------
// Render cart rows
// ----------------------------------------------------------------
function renderEditCart() {
    const container = document.getElementById('itemsContainer');
    if (editCart.length === 0) {
        container.innerHTML = `<div class="text-center py-4" style="color:#9ca3af">
            <i class="bi bi-cart" style="font-size:2rem;display:block;margin-bottom:6px"></i>
            Belum ada produk. Gunakan kolom pencarian di atas untuk menambah item.
        </div>`;
        syncHiddenInputs();
        return;
    }

    container.innerHTML = editCart.map((item, idx) => {
        const subtotal = item.qty * item.final_unit_price;
        const underHpp = Number(item.hpp || 0) > 0 && item.final_unit_price < Number(item.hpp || 0);
        const hppInfo  = underHpp
            ? `<div class="price-warning"><i class="bi bi-exclamation-triangle me-1"></i>Di bawah HPP ${money(item.hpp)}</div>`
            : `<div style="font-size:.7rem;color:#9ca3af;margin-top:2px">HPP: ${money(item.hpp || 0)}</div>`;

        return `<div class="nota-item-card" id="editrow_${idx}">
            <div class="nota-line-head">
                <div class="nota-line-title"><i class="bi bi-bag-check me-1"></i>Item #${idx+1} — ${item.product_name}</div>
                <button type="button" class="remove-line-btn" title="Hapus item" onclick="removeCartItem(${idx})"><i class="bi bi-trash"></i></button>
            </div>
            <div class="nota-line-grid">
                <div>
                    <label class="form-label" style="font-size:.78rem">Produk</label>
                    <div style="font-weight:600;font-size:.86rem">${item.product_name}</div>
                    <div style="font-size:.72rem;color:#059669;margin-top:2px"><i class="bi bi-box-seam me-1"></i>Stok: ${item.stock} ${item.unit}</div>
                    ${hppInfo}
                </div>
                <div>
                    <label class="form-label" style="font-size:.78rem">Qty</label>
                    <input type="number" name="items[${idx}][product_id]" value="${item.product_id}" style="display:none">
                    <input type="number" name="items[${idx}][qty]" class="form-control" min="1"
                           value="${item.qty}" oninput="updateCartQty(${idx}, this.value); calc()" required>
                </div>
                <div>
                    <label class="form-label" style="font-size:.78rem">Harga Akhir</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="items[${idx}][final_unit_price]" class="form-control rupiah-input ${underHpp?'is-invalid':''}"
                               value="${Number(item.final_unit_price).toLocaleString('id-ID')}"
                               oninput="updateCartPrice(${idx}, this.value)" required>
                    </div>
                    ${item.selling_price !== item.final_unit_price ? `<div style="font-size:.7rem;color:#9ca3af;text-decoration:line-through;margin-top:2px">${money(item.selling_price)}</div>` : ''}
                </div>
                <div class="total-box">
                    <span class="total-label">Subtotal</span>
                    <strong class="line-total">${money(subtotal)}</strong>
                </div>
            </div>
        </div>`;
    }).join('');

    syncHiddenInputs();
    updateUnderHppEditBox();
}

function syncHiddenInputs() {
    // Hidden inputs are rendered inline in renderEditCart already (name="items[idx][...]")
    // No separate sync needed
}

// ----------------------------------------------------------------
// Summary / calc
// ----------------------------------------------------------------
function calc() {
    let subtotal = 0, qtyTotal = 0;
    editCart.forEach(item => {
        qtyTotal += Number(item.qty || 0);
        subtotal += Number(item.qty || 0) * Number(item.final_unit_price || 0);
    });

    // Member discount
    const discPct = getMemberDiscountPct();
    const discAmt = Math.round(subtotal * discPct / 100);
    document.getElementById('edit_discount_percent').value = discPct;

    const totalAfterDisc = Math.max(0, subtotal - discAmt);

    // Redeem
    const cust           = getSelectedCustomer();
    const pointBalance   = cust ? Number(cust.point_balance || 0) : 0;
    const redeemInput    = document.getElementById('edit_redeem_points');
    const requestedPoints = Math.floor(Number(redeemInput?.value || 0));
    const maxByPercent   = Math.floor(totalAfterDisc * (redeemRule.maxPercent / 100));
    const maxPoints      = redeemRule.pointValue > 0
        ? Math.min(Math.floor(maxByPercent / redeemRule.pointValue), Math.floor(pointBalance))
        : 0;

    let redeemAmount = 0;
    let warning = '';

    if (requestedPoints > 0) {
        if (!cust) {
            warning = 'Redeem poin hanya bisa digunakan untuk member.';
        } else if (requestedPoints < redeemRule.minimumPoints) {
            warning = `Minimal redeem ${Number(redeemRule.minimumPoints).toLocaleString('id-ID')} poin.`;
        } else if (requestedPoints > pointBalance) {
            warning = `Saldo poin tidak cukup. Saldo tersedia ${pointBalance.toLocaleString('id-ID')} poin.`;
        } else if (requestedPoints > maxPoints) {
            warning = `Maksimal redeem transaksi ini ${maxPoints.toLocaleString('id-ID')} poin.`;
        } else {
            redeemAmount = Math.min(totalAfterDisc, requestedPoints * redeemRule.pointValue);
        }
    }

    const totalBayar = Math.max(0, totalAfterDisc - redeemAmount);
    const cashInputStr = document.getElementById('edit_cash_received')?.value || '0';
    const cash = Number(cashInputStr.replace(/\./g, ''));
    const change = Math.max(0, cash - totalBayar);

    // UI update
    document.getElementById('summaryQty').textContent       = qtyTotal.toLocaleString('id-ID');
    document.getElementById('summarySubtotal').textContent  = money(subtotal);
    document.getElementById('summaryDiscPct').textContent   = discPct;
    document.getElementById('summaryDiscount').textContent  = '-' + money(discAmt);
    document.getElementById('memberDiscountRow').style.display = discAmt > 0 ? 'flex' : 'none';
    document.getElementById('summaryRedeem').textContent    = '-' + money(redeemAmount);
    document.getElementById('summaryTotal').textContent     = money(totalBayar);
    document.getElementById('summaryChange').textContent    = money(change);
    document.getElementById('edit_redeem_info').textContent = cust
        ? `Saldo ${pointBalance.toLocaleString('id-ID')} poin • 1 poin = ${money(redeemRule.pointValue)} • maks ${maxPoints.toLocaleString('id-ID')} poin`
        : 'Redeem hanya aktif jika nota memakai member.';

    const warnEl = document.getElementById('editRedeemWarning');
    warnEl.textContent     = warning;
    warnEl.style.display   = warning ? 'block' : 'none';

    updateUnderHppEditBox();
}

// ----------------------------------------------------------------
// Under-HPP box
// ----------------------------------------------------------------
function updateUnderHppEditBox() {
    const belowItems = editCart.filter(item =>
        Number(item.hpp || 0) > 0 && Number(item.final_unit_price || 0) < Number(item.hpp || 0)
    );
    const box  = document.getElementById('editUnderHppBox');
    const msg  = document.getElementById('editUnderHppMessage');
    const auth = document.getElementById('editUnderHppAuth');
    if (!belowItems.length) { box.style.display = 'none'; auth.style.display = 'none'; return; }
    box.style.display = 'block';
    const names = belowItems.map(i => `${i.product_name} (${money(i.final_unit_price)} < HPP ${money(i.hpp)})`).join(', ');
    if (canUnderHppWithoutApproval) {
        msg.textContent = `Item di bawah HPP: ${names}. Revisi boleh disimpan oleh admin/pemilik dan akan tercatat di log.`;
        auth.style.display = 'none';
    } else {
        msg.textContent = `Item di bawah HPP: ${names}. Isi otorisasi admin/pemilik sebelum menyimpan.`;
        auth.style.display = 'block';
    }
}

// ----------------------------------------------------------------
// Validate before submit — sync hidden inputs from editCart
// ----------------------------------------------------------------
async function validateEditBeforeSubmit() {
    if (editCart.length === 0) { Swal.fire({icon:'warning', title:'Perhatian', text:'Minimal satu item harus ada di nota.'}); return; }

    const below = editCart.filter(item =>
        Number(item.hpp || 0) > 0 && Number(item.final_unit_price || 0) < Number(item.hpp || 0)
    );
    if (below.length) {
        if (canUnderHppWithoutApproval) {
            const result = await Swal.fire({
                title: 'Harga di bawah HPP',
                text: 'Ada item dengan harga di bawah HPP. Revisi tetap disimpan dan masuk log sistem?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            });
            if(!result.isConfirmed) return;
        } else {
            if (!document.getElementById('edit_under_hpp_admin_email').value ||
                !document.getElementById('edit_under_hpp_admin_password').value) {
                Swal.fire({icon:'error', title:'Otorisasi Diperlukan', text:'Harga di bawah HPP membutuhkan otorisasi admin/pemilik. Isi username/email dan password admin terlebih dahulu.'});
                return;
            }
        }
    }
    document.querySelector('form[action="{{ route("kasir.update",$transaction) }}"]').submit();
}

// ----------------------------------------------------------------
// Product search autocomplete
// ----------------------------------------------------------------
let searchTimer;
const searchInput   = document.getElementById('edit_product_search');
const searchResults = document.getElementById('edit_product_results');

searchInput.addEventListener('focus', () => doSearch(searchInput.value.trim()));
searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => doSearch(searchInput.value.trim()), 180);
});
document.addEventListener('click', (e) => {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

function doSearch(q) {
    const term = q.toLowerCase();
    const filtered = products.filter(p =>
        p.name.toLowerCase().includes(term) ||
        p.code.toLowerCase().includes(term)
    ).slice(0, 20);

    if (!filtered.length) {
        searchResults.innerHTML = `<div class="prod-search-item" style="color:#9ca3af">Produk tidak ditemukan</div>`;
        searchResults.style.display = 'block';
        return;
    }

    searchResults.innerHTML = filtered.map(p => `
        <div class="prod-search-item" onclick="selectProduct(${p.id})">
            <div>
                <div class="pi-name">${p.name}</div>
                <div class="pi-meta">${p.code} &bull; Stok: ${p.stock} ${p.unit} &bull; HPP: ${money(p.hpp || 0)}</div>
            </div>
            <div class="pi-price">${money(p.price)}</div>
        </div>`).join('');
    searchResults.style.display = 'block';
}

async function selectProduct(productId) {
    searchResults.style.display = 'none';
    searchInput.value = '';
    const product = products.find(p => p.id === productId);
    if (!product) return;
    await addProductToCart(product);
}

function openProductSearch() {
    searchInput.focus();
    doSearch('');
}

// ----------------------------------------------------------------
// Bootstrap: load existing items
// ----------------------------------------------------------------
async function loadExistingItems() {
    for (const item of existing) {
        const product = products.find(p => String(p.id) === String(item.product_id));
        if (!product) continue;
        editCart.push({
            product_id:       product.id,
            product_name:     product.name,
            unit:             product.unit,
            selling_price:    product.price,
            hpp:              product.hpp || 0,
            final_unit_price: Number(item.final_unit_price),
            stock:            product.stock,
            qty:              Number(item.qty),
        });
    }
    renderEditCart();
    updateMemberInfo();
    calc();
}

loadExistingItems();
</script>
@endpush
