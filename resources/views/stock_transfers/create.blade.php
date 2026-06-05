@extends('layouts.app')
@section('title','Transfer Stok')
@section('page_title','Transfer Stok Gudang')

@push('styles')
<style>
.transfer-row { background:#f8fffb; border:1px solid #cdebd8; border-radius:14px; padding:16px; margin-bottom:12px; box-shadow:0 2px 8px rgba(22,101,52,.04); }
.transfer-row:hover { border-color:#86efac; background:#f0fdf4; }
.transfer-line-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.transfer-line-title { font-size:.78rem; font-weight:800; color:#166534; text-transform:uppercase; letter-spacing:.45px; }
.transfer-line-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(110px,140px); gap:14px; align-items:end; }
.transfer-line-grid > div { min-width:0; }
.transfer-line-grid .form-select, .transfer-line-grid .form-control { min-width:0; }
.remove-row { background:#fff1f2; color:#dc2626; border:1px solid #fecdd3; border-radius:9px; width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:.15s; }
.remove-row:hover { background:#ffe4e6; border-color:#fb7185; }
@media (max-width:576px){ .transfer-line-grid{ grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('stock-transfers.index') }}">Transfer Stok</a></li>
        <li class="breadcrumb-item active">Transfer Baru</li>
    </ol>
</nav>

<form method="POST" action="{{ route('stock-transfers.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2" style="color:#16a34a;"></i>Informasi Transfer</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Dari Gudang <span class="text-danger">*</span></label>
                            <select name="from_warehouse_id" class="form-select @error('from_warehouse_id') is-invalid @enderror" required>
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

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0"><i class="bi bi-box-seam me-2" style="color:#16a34a;"></i>Produk Dipindahkan</h6>
                    <button type="button" onclick="addRow()" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                        <i class="bi bi-plus-lg"></i> Tambah Produk
                    </button>
                </div>
                <div class="card-body p-4">
                    <div id="itemsContainer"></div>
                    <button type="button" onclick="addRow()" class="btn btn-outline-secondary w-100 mt-2" style="border-style:dashed; border-radius:10px; font-size:.85rem;">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Baris Produk
                    </button>
                </div>
            </div>
        </div>
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
$transferProducts = $products->map(fn($p) => [
    'id'   => $p->id,
    'name' => $p->product_name,
    'code' => $p->product_code,
    'unit' => $p->unit,
])->values();
@endphp
const transferProducts = @json($transferProducts);
let trRowIndex = 0;
function addRow() {
    const idx  = trRowIndex++;
    const number = idx + 1;
    const opts = transferProducts.map(p => `<option value="${p.id}">${p.name} (${p.code})</option>`).join('');
    const html = `
    <div class="transfer-row" id="row_${idx}">
        <div class="transfer-line-head">
            <div class="transfer-line-title"><i class="bi bi-arrow-left-right me-1"></i>Produk #${number}</div>
            <button type="button" class="remove-row" title="Hapus baris" onclick="removeRow(${idx})"><i class="bi bi-trash"></i></button>
        </div>
        <div class="transfer-line-grid">
            <div>
                <label class="form-label mb-1">Produk <span class="text-danger">*</span></label>
                <select name="items[${idx}][product_id]" class="form-select" required>
                    <option value="">— Pilih Produk —</option>
                    ${opts}
                </select>
            </div>
            <div>
                <label class="form-label mb-1">Qty <span class="text-danger">*</span></label>
                <input type="number" name="items[${idx}][qty]" class="form-control" min="1" value="1" required>
            </div>
        </div>
    </div>`;
    document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
}
function removeRow(idx) {
    document.getElementById(`row_${idx}`)?.remove();
}
addRow();
</script>
@endpush
