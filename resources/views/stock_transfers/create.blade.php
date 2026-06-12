@extends('layouts.app')
@section('title','Transfer Stok')
@section('page_title','Transfer Stok Gudang')

@push('styles')
<style>
.transfer-row { background:#f8fffb; border:1px solid #cdebd8; border-radius:14px; padding:16px; margin-bottom:12px; box-shadow:0 2px 8px rgba(22,101,52,.04); }
.transfer-row:hover { border-color:#86efac; background:#f0fdf4; }
.transfer-line-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.transfer-line-title { font-size:.78rem; font-weight:800; color:#166534; text-transform:uppercase; letter-spacing:.45px; }
.transfer-line-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(110px,140px); gap:14px; align-items:start; }
.transfer-line-grid > div { min-width:0; }
.transfer-line-grid .form-control { min-width:0; }
.remove-row { background:#fff1f2; color:#dc2626; border:1px solid #fecdd3; border-radius:9px; width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:.15s; }
.remove-row:hover { background:#ffe4e6; border-color:#fb7185; }
.product-search-wrap { position:relative; }
.product-dropdown { position:absolute; z-index:1055; left:0; right:0; top:calc(100% + 4px); max-height:260px; overflow-y:auto; background:#fff; border:1px solid #cbd5e1; border-radius:12px; box-shadow:0 16px 40px rgba(15,23,42,.16); display:none; }
.product-option { padding:10px 12px; cursor:pointer; border-bottom:1px solid #f1f5f9; display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
.product-option:last-child { border-bottom:0; }
.product-option:hover { background:#f0fdf4; }
.product-option-name { font-weight:700; color:#111827; font-size:.9rem; }
.product-option-meta { color:#64748b; font-size:.78rem; margin-top:2px; }
.product-option-stock { font-size:.78rem; white-space:nowrap; color:#166534; font-weight:700; }
.product-option-empty { padding:12px; color:#64748b; font-size:.85rem; text-align:center; }
.selected-product-hint { font-size:.78rem; color:#166534; font-weight:700; margin-top:5px; display:none; }
@media (max-width:576px){ .transfer-line-grid{ grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<!-- Breadcrumb Navigasi -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('stock-transfers.index') }}">Transfer Stok</a></li>
        <li class="breadcrumb-item active">Transfer Baru</li>
    </ol>
</nav>

<!-- Form Pembuatan Transfer Stok -->
<form method="POST" action="{{ route('stock-transfers.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            <!-- Kartu Informasi Transfer -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2" style="color:#16a34a;"></i>Informasi Transfer</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Dari Gudang <span class="text-danger">*</span></label>
                            <select name="from_warehouse_id" id="fromWarehouseSelect" class="form-select @error('from_warehouse_id') is-invalid @enderror" required onchange="renderAllDropdowns()">
                                <option value="">— Pilih Gudang Asal —</option>
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ old('from_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->code }}{{ $wh->is_store ? ' (Utama)' : '' }} - {{ $wh->name }}</option>
                                @endforeach
                            </select>
                            @error('from_warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ke Gudang <span class="text-danger">*</span></label>
                            <select name="to_warehouse_id" class="form-select @error('to_warehouse_id') is-invalid @enderror" required>
                                <option value="">— Pilih Gudang Tujuan —</option>
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->code }}{{ $wh->is_store ? ' (Utama)' : '' }} - {{ $wh->name }}</option>
                                @endforeach
                            </select>
                            @error('to_warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Transfer <span class="text-danger">*</span></label>
                            <input type="date" name="transfer_date" class="form-control @error('transfer_date') is-invalid @enderror" value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                            @error('transfer_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan (opsional)</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Keterangan tambahan...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Daftar Produk Transfer -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0"><i class="bi bi-box-seam me-2" style="color:#16a34a;"></i>Produk Dipindahkan</h6>
                    <div class="d-flex gap-2">
                        <button type="button" onclick="resetTransferForm()" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                        <button type="button" onclick="addRow()" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                            <i class="bi bi-plus-lg"></i> Tambah Produk
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info py-2" style="font-size:.86rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Klik kolom produk untuk membuka dropdown. Jika produk yang sama dipilih lagi, sistem akan menambah qty pada baris yang sudah ada.
                    </div>
                    <div id="itemsContainer"></div>
                    <button type="button" onclick="addRow()" class="btn btn-outline-secondary w-100 mt-2" style="border-style:dashed; border-radius:10px; font-size:.85rem;">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Baris Produk
                    </button>
                </div>
            </div>
        </div>
        <!-- Kolom Kanan: Tombol Simpan -->
        <div class="col-lg-4">
            <div class="card" style="position:sticky; top:76px;">
                <div class="card-body p-4">
                    <p style="font-size:.85rem;color:#6b7280;">Pastikan stok di gudang asal mencukupi untuk setiap produk yang dipindahkan.</p>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-700">
                        <i class="bi bi-check-circle me-2"></i>Simpan Transfer
                    </button>
                    <a href="{{ route('stock-transfers.index') }}" class="btn btn-outline-secondary w-100 mt-2">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
@php
$transferProducts = $products->map(function ($p) {
    return [
        'id'     => $p->id,
        'name'   => $p->product_name,
        'code'   => $p->product_code,
        'unit'   => $p->unit,
        'stocks' => $p->warehouseStocks->mapWithKeys(fn($ws) => [$ws->warehouse_id => (int) $ws->stock]),
    ];
})->values();
@endphp
// Konversi data produk dari PHP ke format JSON JavaScript
const transferProducts = @json($transferProducts);
// Variabel global untuk index baris produk
let trRowIndex = 0;

// Fungsi untuk mendapatkan ID gudang asal yang dipilih
function getFromWarehouseId() {
    return document.getElementById('fromWarehouseSelect')?.value || '';
}
// Fungsi untuk mengambil stok produk berdasarkan gudang asal
function stockForProduct(product) {
    const warehouseId = getFromWarehouseId();
    return Number(product.stocks?.[warehouseId] ?? 0);
}
// Fungsi untuk memformat label produk pada input dropdown
function productLabel(product) {
    const stock = stockForProduct(product);
    return `${product.name} (${product.code})`;
}
// Fungsi untuk mencari baris produk yang sudah ada di daftar
function findExistingProductRow(productId, exceptIdx = null) {
    const inputs = document.querySelectorAll('.transfer-product-id');
    for (const input of inputs) {
        const rowIdx = input.dataset.idx;
        if (exceptIdx !== null && String(rowIdx) === String(exceptIdx)) continue;
        if (String(input.value) === String(productId)) return rowIdx;
    }
    return null;
}
// Fungsi untuk mengurutkan/menomori ulang baris produk
function renumberRows() {
    document.querySelectorAll('.transfer-row').forEach((row, i) => {
        const title = row.querySelector('.transfer-line-title');
        if (title) title.innerHTML = `<i class="bi bi-arrow-left-right me-1"></i>Produk #${i + 1}`;
    });
}
// Fungsi utama untuk menambahkan baris produk baru
function addRow(selectedProduct = null, qty = 1) {
    if (selectedProduct) {
        const existingIdx = findExistingProductRow(selectedProduct.id);
        if (existingIdx !== null) {
            const qtyInput = document.querySelector(`#row_${existingIdx} .transfer-qty`);
            qtyInput.value = Number(qtyInput.value || 0) + Number(qty || 1);
            document.getElementById(`row_${existingIdx}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
    }

    const idx  = trRowIndex++;
    const productValue = selectedProduct ? selectedProduct.id : '';
    const searchValue = selectedProduct ? productLabel(selectedProduct) : '';
    const hintText = selectedProduct ? `Terpilih: ${selectedProduct.name} • Stok asal: ${stockForProduct(selectedProduct)} ${selectedProduct.unit}` : '';
    const html = `
    <div class="transfer-row" id="row_${idx}">
        <div class="transfer-line-head">
            <div class="transfer-line-title"><i class="bi bi-arrow-left-right me-1"></i>Produk #${idx + 1}</div>
            <button type="button" class="remove-row" title="Hapus baris" onclick="removeRow(${idx})"><i class="bi bi-trash"></i></button>
        </div>
        <div class="transfer-line-grid">
            <div>
                <label class="form-label mb-1">Produk <span class="text-danger">*</span></label>
                <div class="product-search-wrap">
                    <input type="hidden" name="items[${idx}][product_id]" class="transfer-product-id" data-idx="${idx}" value="${productValue}" required>
                    <input type="text" class="form-control transfer-product-search" id="productSearch_${idx}" autocomplete="off" placeholder="Klik atau ketik nama/kode produk..." value="${searchValue}" onfocus="openProductDropdown(${idx})" onclick="openProductDropdown(${idx})" oninput="filterProductDropdown(${idx})" required>
                    <div class="product-dropdown" id="productDropdown_${idx}"></div>
                    <div class="selected-product-hint" id="productHint_${idx}" style="display:${selectedProduct ? 'block' : 'none'}">${hintText}</div>
                </div>
            </div>
            <div>
                <label class="form-label mb-1">Qty <span class="text-danger">*</span></label>
                <input type="number" name="items[${idx}][qty]" class="form-control transfer-qty" min="1" value="${qty}" required>
            </div>
        </div>
    </div>`;
    document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
    renumberRows();
}
// Fungsi untuk menghapus baris produk
function removeRow(idx) {
    document.getElementById(`row_${idx}`)?.remove();
    renumberRows();
}
// Fungsi filter produk berdasarkan input pencarian
function filteredProducts(idx) {
    const q = (document.getElementById(`productSearch_${idx}`)?.value || '').toLowerCase().trim();
    return transferProducts.filter(p => !q || p.name.toLowerCase().includes(q) || String(p.code).toLowerCase().includes(q)).slice(0, 40);
}
// Fungsi merender isi dropdown pencarian produk
function renderProductDropdown(idx) {
    const dropdown = document.getElementById(`productDropdown_${idx}`);
    const items = filteredProducts(idx);
    if (!dropdown) return;
    if (items.length === 0) {
        dropdown.innerHTML = '<div class="product-option-empty">Produk tidak ditemukan</div>';
        return;
    }
    dropdown.innerHTML = items.map(p => {
        const stock = stockForProduct(p);
        const stockClass = stock <= 0 ? 'style="color:#dc2626"' : '';
        return `<div class="product-option" onmousedown="event.preventDefault(); selectProduct(${idx}, ${p.id})">
            <div>
                <div class="product-option-name">${p.name}</div>
                <div class="product-option-meta">${p.code} • ${p.unit}</div>
            </div>
            <div class="product-option-stock" ${stockClass}>Stok: ${stock}</div>
        </div>`;
    }).join('');
}
// Fungsi membuka dropdown pencarian produk
function openProductDropdown(idx) {
    document.querySelectorAll('.product-dropdown').forEach(el => el.style.display = 'none');
    renderProductDropdown(idx);
    const dropdown = document.getElementById(`productDropdown_${idx}`);
    if (dropdown) dropdown.style.display = 'block';
}
// Fungsi filter isi dropdown saat mengetik
function filterProductDropdown(idx) {
    const hidden = document.querySelector(`#row_${idx} .transfer-product-id`);
    if (hidden) hidden.value = '';
    const hint = document.getElementById(`productHint_${idx}`);
    if (hint) hint.style.display = 'none';
    renderProductDropdown(idx);
    const dropdown = document.getElementById(`productDropdown_${idx}`);
    if (dropdown) dropdown.style.display = 'block';
}
// Fungsi memilih produk dari dropdown
function selectProduct(idx, productId) {
    const product = transferProducts.find(p => Number(p.id) === Number(productId));
    if (!product) return;

    const existingIdx = findExistingProductRow(product.id, idx);
    if (existingIdx !== null) {
        const qtyCurrent = document.querySelector(`#row_${idx} .transfer-qty`);
        const qtyExisting = document.querySelector(`#row_${existingIdx} .transfer-qty`);
        qtyExisting.value = Number(qtyExisting.value || 0) + Number(qtyCurrent?.value || 1);
        removeRow(idx);
        document.getElementById(`row_${existingIdx}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    document.querySelector(`#row_${idx} .transfer-product-id`).value = product.id;
    document.getElementById(`productSearch_${idx}`).value = productLabel(product);
    const hint = document.getElementById(`productHint_${idx}`);
    if (hint) {
        hint.innerHTML = `Terpilih: ${product.name} • Stok asal: ${stockForProduct(product)} ${product.unit}`;
        hint.style.display = 'block';
    }
    document.getElementById(`productDropdown_${idx}`).style.display = 'none';
}
// Fungsi untuk merender ulang semua dropdown (biasanya dipanggil saat gudang asal berubah)
function renderAllDropdowns() {
    document.querySelectorAll('.transfer-product-id').forEach(hidden => {
        const idx = hidden.dataset.idx;
        if (!hidden.value) return;
        const product = transferProducts.find(p => Number(p.id) === Number(hidden.value));
        if (!product) return;
        const hint = document.getElementById(`productHint_${idx}`);
        if (hint) {
            hint.innerHTML = `Terpilih: ${product.name} • Stok asal: ${stockForProduct(product)} ${product.unit}`;
            hint.style.display = 'block';
        }
    });
}
// Event listener untuk menutup dropdown saat klik di luar area
document.addEventListener('click', function(e) {
    if (!e.target.closest('.product-search-wrap')) {
        document.querySelectorAll('.product-dropdown').forEach(el => el.style.display = 'none');
    }
});

// Fungsi untuk mereset seluruh formulir
function resetTransferForm() {
    Swal.fire({
        title: 'Reset Formulir?',
        text: 'Anda yakin ingin mereset formulir dan mengosongkan keranjang transfer?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    }).then((r) => {
        if (r.isConfirmed) window.location.reload();
    });
}

addRow();
</script>
@endpush
