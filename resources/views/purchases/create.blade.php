@extends('layouts.app')
@section('title', 'Input Pembelian')
@section('page_title', 'Input Pembelian / Restok')

@push('styles')
<style>
    .purchase-item-card {
        background:#f8fffb;
        border:1px solid #cdebd8;
        border-radius:14px;
        padding:16px;
        margin-bottom:12px;
        box-shadow:0 2px 8px rgba(22,101,52,.04);
    }
    .purchase-item-card:hover { border-color:#86efac; background:#f0fdf4; }
    .line-item-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
    .line-item-title { font-size:.78rem; font-weight:800; color:#166534; text-transform:uppercase; letter-spacing:.45px; }
    .purchase-line-grid {
        display:grid;
        grid-template-columns:minmax(120px,.45fr) minmax(190px,.8fr) minmax(150px,.55fr);
        gap:14px;
        align-items:end;
    }
    .purchase-line-grid > div { min-width:0; }
    .purchase-line-grid .product-field { grid-column:1 / -1; min-width:0; }
    .product-field .input-group { flex-wrap:nowrap; }
    .product-field .search-field { min-width:0; }
    .product-field .btn { white-space:nowrap; }
    .product-field .form-select,
    .purchase-line-grid .form-control,
    .purchase-line-grid .input-group { min-width:0; }
    .field-help { font-size:.78rem; color:#6b7280; margin-top:5px; }
    .total-box {
        min-height:40px;
        padding:8px 12px;
        border-radius:10px;
        background:#fff;
        border:1px solid #d1fae5;
        text-align:right;
        display:flex;
        flex-direction:column;
        justify-content:center;
    }
    .total-box .total-label { font-size:.72rem; color:#6b7280; line-height:1.1; }
    .total-box .total-value { font-size:1rem; font-weight:800; color:#14532d; white-space:nowrap; line-height:1.25; }
    .remove-row {
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
    .remove-row:hover { background:#ffe4e6; border-color:#fb7185; }

    .autocomplete-wrap { position:relative; }
    .autocomplete-menu {
        position:absolute;
        top:100%;
        left:0;
        right:0;
        z-index:1050;
        background:#fff;
        border:1px solid #d1d5db;
        border-radius:0 0 10px 10px;
        box-shadow:0 12px 24px rgba(15,23,42,.12);
        max-height:260px;
        overflow:auto;
        display:none;
    }
    .autocomplete-item { padding:10px 12px; cursor:pointer; border-bottom:1px solid #f3f4f6; }
    .autocomplete-item:hover { background:#ecfdf5; }
    .autocomplete-title { font-weight:700; color:#0f172a; }
    .autocomplete-meta { font-size:.78rem; color:#64748b; margin-top:2px; }
    .autocomplete-empty { padding:10px 12px; color:#64748b; }
    @media (max-width: 1200px) {
        .purchase-line-grid { grid-template-columns:minmax(90px,.45fr) minmax(180px,.8fr) minmax(140px,.55fr); }
    }
    @media (max-width: 768px) {
        .purchase-line-grid { grid-template-columns:1fr 1fr; }
        .purchase-line-grid .total-box { grid-column:1 / -1; }
    }
    @media (max-width: 576px) {
        .purchase-line-grid { grid-template-columns:1fr; }
        .total-box { text-align:left; }
    }
    .purchase-line-grid.admin-grid {
        grid-template-columns: minmax(0, 1fr) minmax(110px, 150px);
        align-items: start;
    }
    .purchase-line-grid.admin-grid .product-field {
        grid-column: auto;
    }
</style>
@endpush

@section('content')
<!-- Header Halaman -->
<div class="page-hdr"><div class="page-hdr-left"><h1><i class="bi bi-receipt-cutoff me-2" style="color:var(--primary)"></i>Input Pembelian / Restok</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Pembelian</a></li><li class="breadcrumb-item active">Input Baru</li></ol></nav></div></div>
<!-- Form Input Pembelian -->
<form method="POST" action="{{ route('purchases.store') }}" id="purchase-form">@csrf
<!-- Kontainer Utama -->
<div class="row g-3">
    <!-- Kolom Kiri: Form & Daftar Barang -->
    <div class="col-lg-8">
        <!-- Kartu Informasi Faktur -->
        <div class="card"><div class="card-header"><h6>Informasi Faktur Supplier</h6></div><div class="card-body row g-3">
            <div class="col-md-6 col-xxl-4"><label class="form-label">No. Faktur Supplier <span class="text-danger">*</span></label><input type="text" name="invoice_number" id="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number') }}" placeholder="Sesuai surat jalan/faktur supplier" required>@error('invoice_number')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">Diisi manual sesuai nota/faktur yang diterima.</div></div>
            <div class="col-md-6 col-xxl-3"><label class="form-label">Supplier <span class="text-danger">*</span></label><div class="input-group"><select name="supplier_id" id="supplier_id" class="form-select" required><option value="">— Pilih Supplier —</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>@endforeach</select><a href="{{ route('suppliers.create', ['return_to' => 'purchases.create']) }}" class="btn btn-outline-primary"><i class="bi bi-plus"></i></a></div></div>
            <div class="col-md-6 col-xxl-3"><label class="form-label">Tempat Penyimpanan <span class="text-danger">*</span></label><select name="warehouse_id" id="warehouse_id" class="form-select" required><option value="">— Pilih Gudang/Toko —</option>@foreach($warehouses as $wh)<option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id || (!old('warehouse_id') && $wh->is_store) ? 'selected' : '' }}>{{ $wh->code }}{{ $wh->is_store ? ' (Utama)' : '' }} - {{ $wh->name }}</option>@endforeach</select></div>
            <div class="col-md-6 col-xxl-2"><label class="form-label">Tanggal Beli <span class="text-danger">*</span></label><input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}" required></div>
            <div class="col-12"><label class="form-label">Catatan</label><input type="text" name="notes" id="notes" class="form-control" value="{{ old('notes') }}" placeholder="Keterangan tambahan..."></div>
        </div></div>
        <!-- Kartu Daftar Barang Dibeli -->
        <div class="card mt-3"><div class="card-header d-flex align-items-center justify-content-between"><h6>Daftar Barang Dibeli</h6>
            <div class="d-flex gap-2">
                <button type="button" onclick="window.location.reload()" class="btn btn-sm btn-outline-danger"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>
                <button type="button" onclick="addRow()" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Barang</button>
            </div>
        </div><div class="card-body"><div id="itemsContainer"></div><button type="button" onclick="addRow()" class="btn btn-outline-secondary w-100 mt-2" style="border-style:dashed"><i class="bi bi-plus-circle me-1"></i>Tambah Baris Barang</button></div></div>
    </div>
    <!-- Kolom Kanan: Ringkasan -->
    <div class="col-lg-4">
        <!-- Kartu Ringkasan -->
        <div class="card" style="position:sticky; top:76px"><div class="card-header"><h6>Ringkasan</h6></div><div class="card-body">@if(auth()->user()->role === 'pemilik')<div class="alert alert-info"><strong>HPP otomatis</strong> dihitung ulang saat pembelian disimpan memakai rata-rata tertimbang.</div>@else<div class="alert alert-warning"><strong>Draft restok</strong>: admin tidak melihat/mengisi harga beli. Stok baru bertambah setelah owner mengisi harga dan approve.</div>@endif<div class="d-flex justify-content-between mb-2"><span>Total Item</span><strong id="summaryItems">0 jenis</strong></div><div class="d-flex justify-content-between mb-2"><span>Total Qty</span><strong id="summaryQty">0</strong></div><hr>@if(auth()->user()->role === 'pemilik')<div class="d-flex justify-content-between"><span>Total Pembelian</span><strong class="text-success" id="summaryTotal">Rp 0</strong></div>@endif<button type="submit" class="btn btn-primary w-100 mt-4"><i class="bi bi-check-circle me-2"></i>{{ auth()->user()->role === 'pemilik' ? 'Simpan & Update Stok' : 'Simpan Draft Restok' }}</button><a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary w-100 mt-2">Batal</a></div></div></div>
</div></form>
@endsection

@push('scripts')
@php
$purchaseProducts = $products->map(function ($p) {
    return [
        'id' => $p->id,
        'name' => $p->product_name,
        'code' => $p->product_code,
        'hpp' => (float) $p->hpp,
        'stock' => (int) $p->stock,
        'unit' => $p->unit,
        'last_supplier' => $p->latestPurchaseDetail->purchase->supplier->name ?? 'Belum ada',
    ];
})->values();
@endphp
<script>
// Variabel global untuk form
let rowIndex = 0;
const products = @json($purchaseProducts);
const newSupplierId = @json(session('purchase_new_supplier_id'));
const isOwnerPurchase = @json(auth()->user()->role === 'pemilik');
// Fungsi format uang ke Rupiah
function formatRupiah(value){ return 'Rp ' + Math.round(Number(value||0)).toLocaleString('id-ID'); }
// Fungsi pencarian/filter opsi produk
function searchOptions(keyword){
    const q = String(keyword||'').toLowerCase().trim();
    return products.filter(p => !q || p.name.toLowerCase().includes(q) || p.code.toLowerCase().includes(q)).slice(0,30);
}
// Fungsi menomori ulang baris barang setelah dihapus/ditambah
function renumberRows() {
    document.querySelectorAll('.item-row').forEach((row, i) => {
        const title = row.querySelector('.line-item-title');
        if (title) title.innerHTML = `<i class="bi bi-box-seam me-1"></i>Barang #${i + 1}`;
    });
}
// Fungsi menambah baris baru untuk input produk
function addRow(data=null){
    const idx=rowIndex++;
    const number = idx + 1;
    const html=`<div class="purchase-item-card item-row" id="row_${idx}">
        <div class="line-item-head">
            <div class="line-item-title"><i class="bi bi-box-seam me-1"></i>Barang #${number}</div>
            <button type="button" class="remove-row" title="Hapus baris" onclick="removeRow(${idx})"><i class="bi bi-trash"></i></button>
        </div>
        <div class="purchase-line-grid ${!isOwnerPurchase ? 'admin-grid' : ''}">
            <div class="product-field">
                <label class="form-label mb-1">Produk <span class="text-danger">*</span></label>
                <div class="autocomplete-wrap">
                    <input type="text" class="form-control search-field" placeholder="Ketik/click nama atau kode barang..." autocomplete="off" onfocus="renderProductDropdown(${idx})" oninput="clearSelectedProduct(${idx}); renderProductDropdown(${idx})" onkeydown="if(event.key==='Enter'){event.preventDefault();}">
                    <input type="hidden" name="items[${idx}][product_id]" class="product-id-field">
                    <div class="autocomplete-menu product-menu" id="productMenu_${idx}"></div>
                </div>
                <div class="field-help" id="hppInfo_${idx}">Ketik atau klik field produk, lalu pilih dari dropdown yang muncul.</div>
            </div>
            <div>
                <label class="form-label mb-1">Qty <span class="text-danger">*</span></label>
                <input type="number" name="items[${idx}][qty]" class="form-control qty-field" min="1" value="1" required oninput="calcRow(${idx})">
            </div>
            ${isOwnerPurchase ? `
            <div>
                <label class="form-label mb-1">Harga Beli/satuan <span class="text-danger">*</span></label>
                <div class="input-group"><span class="input-group-text">Rp</span><input type="text" name="items[${idx}][unit_buy_price]" class="form-control price-field rupiah-input" min="0" value="0" required oninput="calcRow(${idx})"></div>
            </div>
            <div class="total-box">
                <span class="total-label">Subtotal</span>
                <span class="total-value" id="subtotal_${idx}">Rp 0</span>
            </div>` : `<input type="hidden" name="items[${idx}][unit_buy_price]" class="price-field" value="0">`}
        </div>
    </div>`;
    document.getElementById('itemsContainer').insertAdjacentHTML('beforeend',html);
    if(data){
        const row=document.getElementById(`row_${idx}`);
        row.querySelector('.search-field').value = data.search || '';
        if(data.product_id){ selectProduct(idx, data.product_id, false); }
        if(data.qty){ row.querySelector('.qty-field').value = data.qty; }
        if(data.unit_buy_price !== undefined && data.unit_buy_price !== null){ row.querySelector('.price-field').value = data.unit_buy_price; }
        calcRow(idx);
    }
    renumberRows(); updateSummary();
}
// Fungsi menyembunyikan semua dropdown pencarian produk
function hideProductMenus(exceptIdx=null){
    document.querySelectorAll('.product-menu').forEach(menu => {
        if(exceptIdx === null || menu.id !== `productMenu_${exceptIdx}`) menu.style.display='none';
    });
}
// Fungsi merender dropdown hasil pencarian produk pada baris tertentu
function renderProductDropdown(idx){
    const row=document.getElementById(`row_${idx}`);
    if(!row) return;
    const keyword=row.querySelector('.search-field').value;
    const matches=searchOptions(keyword);
    const menu=document.getElementById(`productMenu_${idx}`);
    hideProductMenus(idx);
    if(matches.length===0){
        menu.innerHTML='<div class="autocomplete-empty">Produk tidak ditemukan</div>';
    }else{
        menu.innerHTML=matches.map(p=>`<div class="autocomplete-item" onclick="selectProduct(${idx}, ${p.id})">
            <div class="autocomplete-title">${p.name}</div>
            <div class="autocomplete-meta">${p.code} • stok toko ${p.stock} ${p.unit} ${isOwnerPurchase ? '• HPP ' + formatRupiah(p.hpp) : ''}<br><span style="color:var(--primary);font-weight:600"><i class="bi bi-truck me-1"></i>Supplier Terakhir: ${p.last_supplier}</span></div>
        </div>`).join('');
    }
    menu.style.display='block';
}
// Fungsi mengosongkan produk terpilih
function clearSelectedProduct(idx){
    const row=document.getElementById(`row_${idx}`);
    if(!row) return;
    row.querySelector('.product-id-field').value='';
    document.getElementById(`hppInfo_${idx}`).innerHTML = isOwnerPurchase ? 'Pilih produk dari dropdown untuk melihat HPP saat ini.' : 'Ketik atau klik field produk, lalu pilih dari dropdown yang muncul.';
}
// Fungsi mengecek apakah produk sudah ada di baris lain
function findExistingProductRow(productId, exceptIdx = null) {
    const inputs = document.querySelectorAll('.product-id-field');
    for (const input of inputs) {
        const row = input.closest('.item-row');
        if (!row) continue;
        const rowId = row.id.replace('row_', '');
        if (exceptIdx !== null && String(rowId) === String(exceptIdx)) continue;
        if (String(input.value) === String(productId)) return rowId;
    }
    return null;
}
// Fungsi saat pengguna memilih produk dari dropdown
function selectProduct(idx, productId, shouldSave=true){
    const row=document.getElementById(`row_${idx}`);
    const product=products.find(p => String(p.id) === String(productId));
    if(!row || !product) return;
    
    const existingIdx = findExistingProductRow(product.id, idx);
    if (existingIdx !== null) {
        const qtyCurrent = document.querySelector(`#row_${idx} .qty-field`);
        const qtyExisting = document.querySelector(`#row_${existingIdx} .qty-field`);
        qtyExisting.value = Number(qtyExisting.value || 0) + Number(qtyCurrent?.value || 1);
        calcRow(existingIdx);
        removeRow(idx);
        document.getElementById(`row_${existingIdx}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        hideProductMenus();
        return;
    }

    row.querySelector('.product-id-field').value=product.id;
    row.querySelector('.search-field').value=`${product.name} (${product.code})`;
    document.getElementById(`hppInfo_${idx}`).innerHTML = isOwnerPurchase ? `HPP saat ini: <strong>${formatRupiah(product.hpp||0)}</strong> / ${product.unit||''}` : '';
    hideProductMenus();
}
// Fungsi menghitung subtotal satu baris
function calcRow(idx){ const row=document.getElementById(`row_${idx}`); const qty=Number(row.querySelector('.qty-field')?.value||0); const pStr=row.querySelector('.price-field')?.value||'0'; const price=Number(pStr.toString().replace(/\./g,'')); document.getElementById(`subtotal_${idx}`).textContent=formatRupiah(qty*price); updateSummary(); }
// Fungsi menghapus baris produk
function removeRow(idx){ document.getElementById(`row_${idx}`)?.remove(); renumberRows(); updateSummary(); }
// Fungsi menghitung total ringkasan (Item, Qty, Harga)
function updateSummary(){ let rows=document.querySelectorAll('.item-row'),total=0,qty=0; rows.forEach(row=>{const q=Number(row.querySelector('.qty-field')?.value||0); const pStr=row.querySelector('.price-field')?.value||'0'; const p=Number(pStr.toString().replace(/\./g,'')); qty+=q; total+=q*p;}); document.getElementById('summaryItems').textContent=rows.length+' jenis'; document.getElementById('summaryQty').textContent=qty.toLocaleString('id-ID'); document.getElementById('summaryTotal').textContent=formatRupiah(total); }

document.getElementById('purchase-form').addEventListener('submit', function(e){
    const invalidRow=[...document.querySelectorAll('.item-row')].find(row => !row.querySelector('.product-id-field')?.value);
    if(invalidRow){
        e.preventDefault();
        Swal.fire({icon: 'warning', title: 'Perhatian', text: 'Pilih produk dari dropdown terlebih dahulu pada semua baris barang.'});
        invalidRow.querySelector('.search-field')?.focus();
        return false;
    }
});
document.addEventListener('click', function(e){ if(!e.target.closest('.autocomplete-wrap')) hideProductMenus(); });
addRow();
if(newSupplierId){ document.getElementById('supplier_id').value = String(newSupplierId); }
</script>
@endpush
