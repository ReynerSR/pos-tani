@extends('layouts.app')
@section('title', 'Input Stock Opname')
@section('page_title', 'Input Stock Opname')
@push('styles')<style>.opname-row{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px 16px;margin-bottom:8px}.opname-row.changed{border-color:#bbf7d0;background:#f0fdf4}.diff-plus{color:#16a34a;font-weight:700}.diff-minus{color:#dc2626;font-weight:700}.diff-zero{color:#9ca3af}</style>@endpush
@section('content')
<div class="page-hdr"><div class="page-hdr-left"><h1><i class="bi bi-clipboard-check me-2" style="color:var(--primary)"></i>Input Stock Opname</h1></div></div>
<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Pilih lokasi toko/gudang terlebih dahulu. Stok sistem pada daftar produk akan mengikuti lokasi yang dipilih.</div>
<form method="POST" action="{{ route('stock.store') }}">@csrf
<div class="row g-3"><div class="col-lg-9"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h6>Daftar Produk</h6><div class="d-flex gap-2 align-items-center"><input type="text" id="filterInput" class="form-control form-control-sm" style="width:200px" placeholder="Nama produk..."><select id="categoryFilter" class="form-select form-select-sm" style="width:160px" onchange="currentPage=1;renderRows()"><option value="">Semua Kategori</option></select><select id="perPageSelect" class="form-select form-select-sm" style="width:110px" onchange="currentPage=1;renderRows()"><option value="10">10 / hal</option><option value="20" selected>20 / hal</option><option value="50">50 / hal</option><option value="all">Semua</option></select></div></div><div class="card-body" id="productsContainer"></div><div class="card-footer d-flex justify-content-between align-items-center" id="paginationFooter" style="display:none!important"></div></div></div><div class="col-lg-3"><div class="card" style="position:sticky;top:76px"><div class="card-header"><h6>Info Opname</h6></div><div class="card-body"><div class="mb-3"><label class="form-label">Lokasi <span class="text-danger">*</span></label><select name="warehouse_id" id="warehouseSelect" class="form-select" required onchange="renderRows()">@foreach($warehouses as $wh)<option value="{{ $wh->id }}" {{ old('warehouse_id')==$wh->id || (!old('warehouse_id') && $wh->is_store) ? 'selected' : '' }}>{{ $wh->code }}{{ $wh->is_store?' (Utama)':'' }} - {{ $wh->name }}</option>@endforeach</select></div><div class="mb-3"><label class="form-label">Tanggal Opname <span class="text-danger">*</span></label><input type="date" name="adjustment_date" class="form-control" value="{{ old('adjustment_date',date('Y-m-d')) }}" required></div><div class="p-3 rounded mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0"><div class="small text-muted">Produk Berubah</div><div class="fw-bold fs-4 text-success" id="changedCount">0</div></div><button class="btn btn-primary w-100"><i class="bi bi-check-circle me-2"></i>Simpan Opname</button><a href="{{ route('stock.index') }}" class="btn btn-outline-secondary w-100 mt-2">Batal</a></div></div></div></div>
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
const storeWarehouseIds = @json($warehouses->where('is_store', true)->pluck('id')->values());
function stockFor(product, warehouseId){
    const stock = product.stocks[warehouseId];
    if (stock !== undefined && stock !== null) return Number(stock);
    if (storeWarehouseIds.map(String).includes(String(warehouseId))) return Number(product.legacy_stock ?? 0);
    return 0;
}
let currentPage = 1;
function renderRows(){
  const wh = document.getElementById('warehouseSelect').value;
  const q = (document.getElementById('filterInput').value||'').toLowerCase();
  const cat = document.getElementById('categoryFilter').value;
  const perPageVal = document.getElementById('perPageSelect').value;
  const filtered = products.filter(p => (!q || p.name.toLowerCase().includes(q) || p.code.toLowerCase().includes(q)) && (!cat || p.category===cat));
  const total = filtered.length;
  const perPage = perPageVal === 'all' ? total : parseInt(perPageVal);
  const totalPages = perPage > 0 ? Math.max(1, Math.ceil(total / perPage)) : 1;
  if (currentPage > totalPages) currentPage = totalPages;
  const start = (currentPage - 1) * perPage;
  const pageItems = perPageVal === 'all' ? filtered : filtered.slice(start, start + perPage);
  const html = pageItems.map((p, i) => {
    const gi = start + i;
    const stock = stockFor(p, wh);
    const actualValue = Math.max(stock, 0);
    return `<div class="opname-row" id="opRow${gi}" data-original="${stock}"><input type="hidden" name="items[${gi}][product_id]" value="${p.id}"><div class="row g-2 align-items-center"><div class="col-md-5"><strong>${p.name}</strong><div class="small text-muted">${p.code} • ${p.category||'-'}</div></div><div class="col-md-2 text-center"><div class="small text-muted">Stok Sistem</div><strong class="${stock < 0 ? 'diff-minus' : ''}">${stock}</strong><div class="small text-muted">${p.unit}</div></div><div class="col-md-3"><div class="small text-muted mb-1">Stok Aktual</div><input type="text" inputmode="numeric" pattern="[0-9]*" name="items[${gi}][stock_actual]" class="form-control form-control-sm actual-input" value="${actualValue}" data-original="${stock}" data-row="${gi}" oninput="this.value=this.value.replace(/[^0-9]/g,''); calcDiff(this)" onwheel="this.blur()"></div><div class="col-md-2 text-center"><div class="small text-muted">Selisih</div><strong class="diff-zero" id="diff${gi}">0</strong></div></div><div class="mt-2" id="notesRow${gi}" style="display:none"><input type="text" name="items[${gi}][notes]" class="form-control form-control-sm" placeholder="Keterangan perubahan"></div></div>`;
  }).join('');
  document.getElementById('productsContainer').innerHTML = html || '<div class="text-center text-muted py-4">Produk tidak ditemukan</div>';
  // pagination footer
  const footer = document.getElementById('paginationFooter');
  if (perPageVal === 'all' || totalPages <= 1) {
    footer.style.display = 'none';
  } else {
    footer.style.display = 'flex';
    const from = total === 0 ? 0 : start + 1;
    const to = Math.min(start + perPage, total);
    let pages = '';
    const maxBtn = 5;
    let startBtn = Math.max(1, currentPage - Math.floor(maxBtn/2));
    let endBtn = Math.min(totalPages, startBtn + maxBtn - 1);
    if (endBtn - startBtn < maxBtn - 1) startBtn = Math.max(1, endBtn - maxBtn + 1);
    if (startBtn > 1) pages += `<button type="button" class="btn btn-sm btn-outline-secondary" onclick="goPage(1)">1</button><span class="btn btn-sm disabled">…</span>`;
    for (let p = startBtn; p <= endBtn; p++) pages += `<button type="button" class="btn btn-sm ${p===currentPage?'btn-primary':'btn-outline-secondary'}" onclick="goPage(${p})">${p}</button>`;
    if (endBtn < totalPages) pages += `<span class="btn btn-sm disabled">…</span><button type="button" class="btn btn-sm btn-outline-secondary" onclick="goPage(${totalPages})">${totalPages}</button>`;
    footer.innerHTML = `<small class="text-muted">${from}–${to} dari ${total} produk</small><div class="d-flex gap-1">${pages}</div>`;
  }
  updateChangedCount();
}
function goPage(p){ currentPage = p; renderRows(); window.scrollTo({top:0,behavior:'smooth'}); }
function calcDiff(input){ const idx=input.dataset.row; const original=Number(input.dataset.original); const actual=Number(input.value||0); const diff=actual-original; const row=document.getElementById(`opRow${idx}`); const el=document.getElementById(`diff${idx}`); const notes=document.getElementById(`notesRow${idx}`); el.textContent=diff>0?`+${diff}`:String(diff); el.className=diff>0?'diff-plus':(diff<0?'diff-minus':'diff-zero'); row.classList.toggle('changed',diff!==0); notes.style.display=diff!==0?'block':'none'; updateChangedCount(); }
function updateChangedCount(){ document.getElementById('changedCount').textContent=document.querySelectorAll('.opname-row.changed').length; }
document.getElementById('filterInput').addEventListener('input', renderRows);
(function initCategoryFilter(){const cats=[...new Set(products.map(p=>p.category).filter(Boolean))].sort(); document.getElementById('categoryFilter').innerHTML='<option value="">Semua Kategori</option>'+cats.map(c=>`<option value="${c}">${c}</option>`).join('');})();
renderRows();
</script>
@endpush
