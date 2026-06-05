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
    }
    .nota-item-card:hover { border-color:#bbf7d0; background:#fbfffd; }
    .nota-line-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
    .nota-line-title { font-size:.78rem; font-weight:800; color:#166534; text-transform:uppercase; letter-spacing:.45px; }
    .nota-line-grid {
        display:grid;
        grid-template-columns:minmax(110px,.45fr) minmax(190px,.8fr) minmax(150px,.55fr);
        gap:14px;
        align-items:end;
    }
    .nota-line-grid > div { min-width:0; }
    .nota-line-grid .product-field { grid-column:1 / -1; min-width:0; }
    .nota-line-grid .form-select,
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
    }
    .remove-line-btn:hover { background:#ffe4e6; border-color:#fb7185; }
    .under-hpp-edit-box { display:none; background:#fff7ed; border:1px solid #fdba74; border-radius:10px; padding:10px 12px; margin-bottom:12px; }
    .under-hpp-edit-box .title { font-weight:800;color:#9a3412;font-size:.82rem; }
    .under-hpp-edit-box .desc { color:#9a3412;font-size:.75rem;line-height:1.35; }
    .price-warning { font-size:.72rem;color:#b45309;margin-top:4px; }
    @media (max-width: 1200px) {
        .nota-line-grid { grid-template-columns:minmax(90px,.45fr) minmax(180px,.8fr) minmax(140px,.55fr); }
    }
    @media (max-width: 768px) {
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
    <a href="{{ route('kasir.show',$transaction) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
</div>

<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Ketika nota direvisi, sistem akan mengembalikan stok dan poin transaksi lama terlebih dahulu, lalu menghitung ulang stok, total, dan poin sesuai data baru.</div>

<form method="POST" action="{{ route('kasir.update',$transaction) }}">
@csrf @method('PUT')
<input type="hidden" name="discount_percent" value="{{ old('discount_percent', $transaction->discount_percent) }}">
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
                    <select name="customer_id" class="form-select" id="edit_customer_id" onchange="calc()">
                        <option value="">Umum / Non-member</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" data-points="{{ max(0, (float) $customer->point_balance + ($customer->id == $transaction->customer_id ? ((float) ($transaction->points_redeemed ?? 0) - (float) ($transaction->points_earned ?? 0)) : 0)) }}" {{ old('customer_id',$transaction->customer_id)==$customer->id?'selected':'' }}>{{ $customer->full_name }} - {{ $customer->whatsapp_number }} ({{ number_format($customer->point_balance,0,',','.') }} poin)</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Uang Diterima</label>
                    <input type="number" name="cash_received" id="edit_cash_received" class="form-control" value="{{ old('cash_received',$transaction->cash_received) }}" min="0" step="any" oninput="calc()" required>
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
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()"><i class="bi bi-plus-lg me-1"></i>Tambah Item</button>
            </div>
            <div class="card-body">
                <div id="itemsContainer"></div>
                <button type="button" class="btn btn-outline-secondary w-100 mt-2" onclick="addItem()" style="border-style:dashed"><i class="bi bi-plus-circle me-1"></i>Tambah Baris</button>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card" style="position:sticky; top:76px">
            <div class="card-header"><h6>Ringkasan</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span>Total Qty</span><strong id="summaryQty">0</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong id="summarySubtotal">Rp 0</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Potongan Poin</span><strong id="summaryRedeem" class="text-danger">-Rp 0</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Total Bayar</span><strong id="summaryTotal">Rp 0</strong></div>
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
                <button type="submit" class="btn btn-primary w-100" onclick="return validateEditBeforeSubmit()"><i class="bi bi-check-circle me-2"></i>Simpan Revisi Nota</button>
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
        'id' => $p->id,
        'code' => $p->product_code,
        'name' => $p->product_name,
        'price' => (float) $p->selling_price,
        'hpp' => (float) ($p->hpp ?? 0),
        'stock' => (int) $p->stock,
        'unit' => $p->unit,
    ];
})->values();
$existingItems = $transaction->details->map(function ($d) {
    return [
        'product_id' => $d->product_id,
        'qty' => $d->qty,
        'final_unit_price' => (float) $d->final_unit_price,
    ];
})->values();
@endphp
<script>
const currentUserRole = @json(auth()->user()->role);
const canUnderHppWithoutApproval = ['pemilik','admin'].includes(currentUserRole);
const products = @json($editProducts);
const existing = @json($existingItems);
const redeemRule = {
    pointValue: {{ (float) ($rule->redeem_point_value ?? 100) }},
    minimumPoints: {{ (float) ($rule->minimum_redeem_points ?? 100) }},
    maxPercent: {{ (float) ($rule->max_redeem_percent ?? 50) }},
};
let rowIndex = 0;
function productOptions(selected = '') {
    return '<option value="">— Pilih Produk —</option>' + products.map(p => `<option value="${p.id}" data-price="${p.price}" data-hpp="${p.hpp || 0}" ${String(selected)===String(p.id)?'selected':''}>${p.name} (${p.code}) - stok ${p.stock} ${p.unit} - HPP Rp ${Number(p.hpp || 0).toLocaleString('id-ID')}</option>`).join('');
}
function addItem(data = null) {
    const idx = rowIndex++;
    const number = idx + 1;
    const html = `<div class="nota-item-card item-row" id="row_${idx}">
        <div class="nota-line-head">
            <div class="nota-line-title"><i class="bi bi-bag-check me-1"></i>Item #${number}</div>
            <button type="button" class="remove-line-btn" title="Hapus item" onclick="removeRow(${idx})"><i class="bi bi-trash"></i></button>
        </div>
        <div class="nota-line-grid">
            <div class="product-field">
                <label class="form-label">Produk</label>
                <select name="items[${idx}][product_id]" class="form-select" onchange="fillPrice(this,${idx})" required>${productOptions(data?.product_id || '')}</select>
            </div>
            <div>
                <label class="form-label">Qty</label>
                <input type="number" name="items[${idx}][qty]" class="form-control qty" min="1" value="${data?.qty || 1}" oninput="calc()" required>
            </div>
            <div>
                <label class="form-label">Harga Akhir</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="items[${idx}][final_unit_price]" class="form-control price" min="0" step="any" value="${data?.final_unit_price || 0}" oninput="calc()" required>
                </div>
                <div class="price-warning" style="display:none"></div>
            </div>
            <div class="total-box">
                <span class="total-label">Subtotal</span>
                <strong class="line-total">Rp 0</strong>
            </div>
        </div>
    </div>`;
    document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
    if (!data) fillPrice(document.querySelector(`#row_${idx} select`), idx);
    calc();
}
function fillPrice(sel, idx) {
    const opt = sel.selectedOptions[0];
    const price = opt?.dataset?.price || 0;
    const input = document.querySelector(`#row_${idx} .price`);
    if (input && (!input.value || Number(input.value) === 0)) input.value = price;
    calc();
}
function removeRow(idx) { document.getElementById(`row_${idx}`)?.remove(); calc(); }
function getRowProduct(row){
    const select = row.querySelector('select');
    const id = select?.value;
    return products.find(p => String(p.id) === String(id));
}
function rowBelowHpp(row){
    const product = getRowProduct(row);
    const price = Number(row.querySelector('.price')?.value || 0);
    return product && Number(product.hpp || 0) > 0 && price < Number(product.hpp || 0);
}
function updateUnderHppEditBox(){
    const rows = Array.from(document.querySelectorAll('.item-row'));
    const below = rows.filter(rowBelowHpp).map(row => {
        const product = getRowProduct(row);
        const price = Number(row.querySelector('.price')?.value || 0);
        const warning = row.querySelector('.price-warning');
        if(warning){
            warning.style.display = 'block';
            warning.textContent = canUnderHppWithoutApproval ? `Di bawah HPP Rp ${Number(product.hpp || 0).toLocaleString('id-ID')}. Revisi tetap boleh diproses dan akan masuk log.` : `Di bawah HPP Rp ${Number(product.hpp || 0).toLocaleString('id-ID')}. Butuh otorisasi admin/pemilik.`;
        }
        return `${product.name} (Rp ${price.toLocaleString('id-ID')} < HPP Rp ${Number(product.hpp || 0).toLocaleString('id-ID')})`;
    });
    rows.filter(row => !rowBelowHpp(row)).forEach(row => { const warning = row.querySelector('.price-warning'); if(warning){ warning.style.display = 'none'; warning.textContent = ''; } });
    const box = document.getElementById('editUnderHppBox');
    const msg = document.getElementById('editUnderHppMessage');
    const auth = document.getElementById('editUnderHppAuth');
    if(!below.length){ box.style.display = 'none'; auth.style.display = 'none'; return; }
    box.style.display = 'block';
    if(canUnderHppWithoutApproval){
        msg.textContent = `Item di bawah HPP: ${below.join(', ')}. Revisi boleh disimpan oleh admin/pemilik dan akan tercatat di log.`;
        auth.style.display = 'none';
    } else {
        msg.textContent = `Item di bawah HPP: ${below.join(', ')}. Isi otorisasi admin/pemilik sebelum menyimpan.`;
        auth.style.display = 'block';
    }
}
function validateEditBeforeSubmit(){
    const below = Array.from(document.querySelectorAll('.item-row')).filter(rowBelowHpp);
    if(!below.length) return true;
    if(canUnderHppWithoutApproval){
        return confirm('Ada item dengan harga di bawah HPP. Revisi tetap disimpan dan masuk log sistem?');
    }
    if(!document.getElementById('edit_under_hpp_admin_email').value || !document.getElementById('edit_under_hpp_admin_password').value){
        alert('Harga di bawah HPP membutuhkan otorisasi admin/pemilik. Isi username/email dan password admin terlebih dahulu.');
        return false;
    }
    return true;
}
function calc() {
    let total = 0, qtyTotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = Number(row.querySelector('.qty')?.value || 0);
        const price = Number(row.querySelector('.price')?.value || 0);
        const subtotal = qty * price;
        qtyTotal += qty; total += subtotal;
        row.querySelector('.line-total').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    });

    const customerSelect = document.getElementById('edit_customer_id');
    const selectedCustomer = customerSelect?.selectedOptions?.[0];
    const pointBalance = Number(selectedCustomer?.dataset?.points || 0);
    const redeemInput = document.getElementById('edit_redeem_points');
    const requestedPoints = Math.floor(Number(redeemInput?.value || 0));
    const maxByPercent = Math.floor(total * (redeemRule.maxPercent / 100));
    const maxPoints = redeemRule.pointValue > 0 ? Math.min(Math.floor(maxByPercent / redeemRule.pointValue), Math.floor(pointBalance)) : 0;
    let redeemAmount = 0;
    let warning = '';

    if (requestedPoints > 0) {
        if (! selectedCustomer?.value) {
            warning = 'Redeem poin hanya bisa digunakan untuk member.';
        } else if (requestedPoints < redeemRule.minimumPoints) {
            warning = `Minimal redeem ${Number(redeemRule.minimumPoints).toLocaleString('id-ID')} poin.`;
        } else if (requestedPoints > pointBalance) {
            warning = `Saldo poin tidak cukup. Saldo tersedia ${pointBalance.toLocaleString('id-ID')} poin.`;
        } else if (requestedPoints > maxPoints) {
            warning = `Maksimal redeem transaksi ini ${maxPoints.toLocaleString('id-ID')} poin.`;
        } else {
            redeemAmount = Math.min(total, requestedPoints * redeemRule.pointValue);
        }
    }

    const totalBayar = Math.max(0, total - redeemAmount);
    const cash = Number(document.getElementById('edit_cash_received')?.value || 0);
    const change = Math.max(0, cash - totalBayar);

    document.getElementById('summaryQty').textContent = qtyTotal.toLocaleString('id-ID');
    document.getElementById('summarySubtotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('summaryRedeem').textContent = '-Rp ' + redeemAmount.toLocaleString('id-ID');
    document.getElementById('summaryTotal').textContent = 'Rp ' + totalBayar.toLocaleString('id-ID');
    document.getElementById('summaryChange').textContent = 'Rp ' + change.toLocaleString('id-ID');
    document.getElementById('edit_redeem_info').textContent = selectedCustomer?.value
        ? `Saldo ${pointBalance.toLocaleString('id-ID')} poin • 1 poin = Rp ${Number(redeemRule.pointValue).toLocaleString('id-ID')} • maksimal ${maxPoints.toLocaleString('id-ID')} poin`
        : 'Redeem hanya aktif jika nota memakai member.';
    const warningEl = document.getElementById('editRedeemWarning');
    warningEl.textContent = warning;
    warningEl.style.display = warning ? 'block' : 'none';
    updateUnderHppEditBox();
}
(existing.length ? existing : [{}]).forEach(addItem);
</script>
@endpush
