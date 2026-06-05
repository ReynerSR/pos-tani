@extends('layouts.app')
@section('title', 'Input Stock Opname')
@section('page_title', 'Input Stock Opname')
@push('styles')<style>.opname-row{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px 16px;margin-bottom:8px}.opname-row.changed{border-color:#bbf7d0;background:#f0fdf4}.diff-plus{color:#16a34a;font-weight:700}.diff-minus{color:#dc2626;font-weight:700}.diff-zero{color:#9ca3af}</style>@endpush
@section('content')
<div class="page-hdr"><div class="page-hdr-left"><h1><i class="bi bi-clipboard-check me-2" style="color:var(--primary)"></i>Input Stock Opname</h1></div></div>
<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Pilih lokasi toko/gudang terlebih dahulu. Stok sistem pada daftar produk akan mengikuti lokasi yang dipilih.</div>
<form method="POST" action="{{ route('stock.store') }}">@csrf
<div class="row g-3"><div class="col-lg-9"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h6>Daftar Produk</h6><div class="d-flex gap-2"><input type="text" id="filterInput" class="form-control form-control-sm" style="width:220px" placeholder="Nama produk..."><select id="categoryFilter" class="form-select form-select-sm" style="width:180px" onchange="renderRows()"><option value="">Semua Kategori</option></select></div></div><div class="card-body" id="productsContainer"></div></div></div><div class="col-lg-3"><div class="card" style="position:sticky;top:76px"><div class="card-header"><h6>Info Opname</h6></div><div class="card-body"><div class="mb-3"><label class="form-label">Lokasi <span class="text-danger">*</span></label><select name="warehouse_id" id="warehouseSelect" class="form-select" required onchange="renderRows()">@foreach($warehouses as $wh)<option value="{{ $wh->id }}" {{ old('warehouse_id')==$wh->id || (!old('warehouse_id') && $wh->is_store) ? 'selected' : '' }}>{{ $wh->code }}{{ $wh->is_store?' (Utama)':'' }} - {{ $wh->name }}</option>@endforeach</select></div><div class="mb-3"><label class="form-label">Tanggal Opname <span class="text-danger">*</span></label><input type="date" name="adjustment_date" class="form-control" value="{{ old('adjustment_date',date('Y-m-d')) }}" required></div><div class="p-3 rounded mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0"><div class="small text-muted">Produk Berubah</div><div class="fw-bold fs-4 text-success" id="changedCount">0</div></div><button class="btn btn-primary w-100"><i class="bi bi-check-circle me-2"></i>Simpan Opname</button><a href="{{ route('stock.index') }}" class="btn btn-outline-secondary w-100 mt-2">Batal</a></div></div></div></div>
</form>
@endsection
@push('scripts')
@php
$stockProducts = $products->map(function ($p) {
    return [
        'id' => $p->id,
        'name' => $p->product_name,
        'code' => $p->product_code,
        'category' => $p->category,
        'unit' => $p->unit,
        'legacy_stock' => (int) $p->stock,
        'stocks' => $p->warehouseStocks->mapWithKeys(function ($ws) {
            return [$ws->warehouse_id => (int) $ws->stock];
        }),
    ];
})->values();
@endphp
<script>
const products = @json($stockProducts);
function stockFor(product, warehouseId){ return Number(product.stocks[warehouseId] ?? 0); }
function renderRows(){ const wh=document.getElementById('warehouseSelect').value; const q=(document.getElementById('filterInput').value||'').toLowerCase(); const cat=document.getElementById('categoryFilter').value; const html=products.filter(p=>(!q || p.name.toLowerCase().includes(q) || p.code.toLowerCase().includes(q)) && (!cat || p.category===cat)).map((p,i)=>{const stock=stockFor(p,wh); return `<div class="opname-row" id="opRow${i}" data-original="${stock}"><input type="hidden" name="items[${i}][product_id]" value="${p.id}"><div class="row g-2 align-items-center"><div class="col-md-5"><strong>${p.name}</strong><div class="small text-muted">${p.code} • ${p.category||'-'}</div></div><div class="col-md-2 text-center"><div class="small text-muted">Stok Sistem</div><strong>${stock}</strong><div class="small text-muted">${p.unit}</div></div><div class="col-md-3"><div class="small text-muted mb-1">Stok Aktual</div><input type="text" inputmode="numeric" pattern="[0-9]*" name="items[${i}][stock_actual]" class="form-control form-control-sm actual-input" value="${stock}" data-original="${stock}" data-row="${i}" oninput="this.value=this.value.replace(/[^0-9]/g,''); calcDiff(this)" onwheel="this.blur()"></div><div class="col-md-2 text-center"><div class="small text-muted">Selisih</div><strong class="diff-zero" id="diff${i}">0</strong></div></div><div class="mt-2" id="notesRow${i}" style="display:none"><input type="text" name="items[${i}][notes]" class="form-control form-control-sm" placeholder="Keterangan perubahan"></div></div>`}).join(''); document.getElementById('productsContainer').innerHTML=html || '<div class="text-center text-muted py-4">Produk tidak ditemukan</div>'; updateChangedCount(); }
function calcDiff(input){ const idx=input.dataset.row; const original=Number(input.dataset.original); const actual=Number(input.value||0); const diff=actual-original; const row=document.getElementById(`opRow${idx}`); const el=document.getElementById(`diff${idx}`); const notes=document.getElementById(`notesRow${idx}`); el.textContent=diff>0?`+${diff}`:String(diff); el.className=diff>0?'diff-plus':(diff<0?'diff-minus':'diff-zero'); row.classList.toggle('changed',diff!==0); notes.style.display=diff!==0?'block':'none'; updateChangedCount(); }
function updateChangedCount(){ document.getElementById('changedCount').textContent=document.querySelectorAll('.opname-row.changed').length; }
document.getElementById('filterInput').addEventListener('input', renderRows);
(function initCategoryFilter(){const cats=[...new Set(products.map(p=>p.category).filter(Boolean))].sort(); document.getElementById('categoryFilter').innerHTML='<option value="">Semua Kategori</option>'+cats.map(c=>`<option value="${c}">${c}</option>`).join('');})();
renderRows();
</script>
@endpush
