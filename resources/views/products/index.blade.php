@extends('layouts.app')
@section('title','Data Produk')
@section('page_title','Data Produk')

@section('content')

<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-box-seam me-2" style="color:var(--primary)"></i>Data Produk</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Daftar Produk</li></ol></nav>
    </div>
    @if(in_array(auth()->user()->role, ['admin', 'pemilik']))
    <a href="{{ route('products.create') }}" class="btn btn-primary px-4"><i class="bi bi-plus-lg me-2"></i>Tambah Produk</a>
    @endif
</div>

<!-- Kartu Filter dan Pencarian -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" id="products-filter-form" class="row g-2 align-items-end">
            <div class="col-12 col-md-4"><div class="search-bar"><i class="bi bi-search si-search"></i><input type="text" name="search" id="products-search" class="form-control" placeholder="Cari nama / kode produk..." value="{{ request('search') }}" autocomplete="off"></div></div>
            <div class="col-6 col-md-3"><select name="category" class="form-select" onchange="this.form.submit()"><option value="">Semua Kategori</option>@foreach($categories as $cat)<option value="{{ $cat }}" {{ request('category')==$cat?'selected':'' }}>{{ $cat }}</option>@endforeach</select></div>
            <div class="col-6 col-md-3"><select name="status" class="form-select" onchange="this.form.submit()"><option value="">Semua Status</option><option value="active" {{ request('status')=='active'?'selected':'' }}>Aktif</option><option value="low" {{ request('status')=='low'?'selected':'' }}>Stok Kritis</option><option value="empty" {{ request('status')=='empty'?'selected':'' }}>Stok Habis</option><option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Non-aktif</option></select></div>
            <div class="col-6 col-md-1"><select name="per_page" class="form-select" onchange="this.form.submit()"><option value="20"{{ request('per_page', 20)==20?'selected':'' }}>20 Baris</option><option value="50" {{ request('per_page')==50?'selected':'' }}>50 Baris</option><option value="100" {{ request('per_page')==100?'selected':'' }}>100 Baris</option></select></div>
            <div class="col-6 col-md-1 d-flex gap-1"><a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100" title="Reset"><i class="bi bi-x-lg"></i></a></div>
        </form>
    </div>
</div>
@if(auth()->user()->role === 'pemilik')
<!-- Form Manajemen Kategori dan Satuan (Hanya Pemilik) -->
<div class="row mx-0">
    <div class="col-md-6 px-0 pe-md-2">
        <div class="card mb-3">
            <div class="card-body py-3">
                <form method="POST" action="{{ route('products.category.update') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Edit Kategori Lama</label>
                        <div class="d-flex gap-2">
                            <select name="old_category" id="idxCategorySelect" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
                            </select>
                            <button type="button" class="btn btn-outline-danger px-3" onclick="deleteIndexCategory()" title="Hapus Kategori"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="col-8"><label class="form-label">Nama Kategori Baru</label><input type="text" name="new_category" class="form-control" required placeholder="Contoh: PUPUK CAIR" oninput="this.value=this.value.toUpperCase()"></div>
                    <div class="col-4"><button class="btn btn-outline-primary w-100"><i class="bi bi-pencil-square me-1"></i>Update</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6 px-0 ps-md-2">
        <div class="card mb-3">
            <div class="card-body py-3">
                <form method="POST" action="{{ route('products.unit.update') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Edit Satuan Lama</label>
                        <div class="d-flex gap-2">
                            <select name="old_unit" id="idxUnitSelect" class="form-select" required>
                                <option value="">-- Pilih Satuan --</option>
                                @foreach($units as $unit)<option value="{{ $unit }}">{{ $unit }}</option>@endforeach
                            </select>
                            <button type="button" class="btn btn-outline-danger px-3" onclick="deleteIndexUnit()" title="Hapus Satuan"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="col-8"><label class="form-label">Nama Satuan Baru</label><input type="text" name="new_unit" class="form-control" required placeholder="Contoh: KARTON" oninput="this.value=this.value.toUpperCase()"></div>
                    <div class="col-4"><button class="btn btn-outline-primary w-100"><i class="bi bi-pencil-square me-1"></i>Update</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<div id="products-results">
<!-- Kartu Tabel Daftar Produk -->
<div class="card">
    <div class="card-header"><h6 class="mb-0">Daftar Produk <span class="badge bg-success ms-1">{{ $products->total() }}</span></h6></div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead><tr>
                <x-sortable-column column="id" label="#" />
                <x-sortable-column column="product_code" label="Kode" />
                <x-sortable-column column="product_name" label="Nama Produk" />
                <x-sortable-column column="category" label="Kategori" />
                <x-sortable-column column="unit" label="Satuan" />
                <x-sortable-column column="selling_price" label="Harga Jual" />
                @if(auth()->user()->canAccessHPP())<x-sortable-column column="hpp" label="HPP" />@endif
                <th>Stok Semua Tempat</th>
                <x-sortable-column column="stock" label="Stok Toko" />
                <x-sortable-column column="is_active" label="Status" />
                <th style="width:110px">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($products as $i => $p)
                @php $cellBg = $p->stock <= 0 ? '#fef2f2' : ($p->stock <= $p->minimum_stock ? '#fffbeb' : '#f0fdf4'); @endphp
                <tr>
                    <td class="text-muted small">{{ $p->id }}</td>
                    <td><span style="font-family:monospace;font-size:.8rem;background:#f3f4f6;padding:2px 7px;border-radius:5px">{{ $p->product_code }}</span></td>
                    <td><a href="{{ route('products.show',$p) }}" style="font-weight:600;color:var(--primary-dark);text-decoration:none">{{ $p->product_name }}</a></td>
                    <td>{{ $p->category ?? '-' }}</td>
                    <td>{{ $p->unit }}</td>
                    <td><strong>Rp {{ number_format($p->selling_price,0,',','.') }}</strong></td>
                    @if(auth()->user()->canAccessHPP())<td>Rp {{ number_format($p->hpp,0,',','.') }}</td>@endif
                    <td style="min-width:210px">
                        @php $totalSemuaGudang = 0; @endphp
                        @foreach($warehouses as $wh)
                            @php 
                                $ws = $p->warehouseStocks->firstWhere('warehouse_id', $wh->id); 
                                $wStock = $ws->stock ?? 0;
                                $totalSemuaGudang += $wStock;
                                $badgeBg = $wStock <= 0 ? '#fef2f2' : ($wStock <= $p->minimum_stock ? '#fffbeb' : '#f0fdf4');
                                $badgeText = $wStock <= 0 ? '#dc3545' : ($wStock <= $p->minimum_stock ? '#854d0e' : '#198754');
                                $badgeBorder = $wStock <= 0 ? '#f8d7da' : ($wStock <= $p->minimum_stock ? '#ffe69c' : '#d1e7dd');
                            @endphp
                            <span class="badge me-1 mb-1" style="background-color: {{ $badgeBg }}; color: {{ $badgeText }}; border: 1px solid {{ $badgeBorder }};">{{ $wh->code }}{{ $wh->is_store ? ' (Utama)' : '' }}: {{ number_format($wStock) }}</span>
                        @endforeach
                        @php 
                            $totalTextColor = $totalSemuaGudang <= 0 ? '#dc3545' : ($totalSemuaGudang <= $p->minimum_stock ? '#854d0e' : '#198754');
                            $totalBgColor = $totalSemuaGudang <= 0 ? '#fef2f2' : ($totalSemuaGudang <= $p->minimum_stock ? '#fffbeb' : '#f0fdf4');
                            $totalBorderColor = $totalSemuaGudang <= 0 ? '#f8d7da' : ($totalSemuaGudang <= $p->minimum_stock ? '#ffe69c' : '#d1e7dd');
                        @endphp
                        <div class="mt-1"><span class="badge" style="background-color: {{ $totalBgColor }}; color: {{ $totalTextColor }}; border: 1px solid {{ $totalBorderColor }}; font-size:0.85em; font-weight: 700; text-decoration: underline; text-underline-offset: 3px;">Total Keseluruhan: {{ number_format($totalSemuaGudang) }}</span></div>
                    </td>
                    @php 
                        $textBg = $p->stock <= 0 ? '#dc3545' : ($p->stock <= $p->minimum_stock ? '#854d0e' : '#198754'); 
                        $borderBg = $p->stock <= 0 ? '#f8d7da' : ($p->stock <= $p->minimum_stock ? '#ffe69c' : '#d1e7dd');
                    @endphp
                    <td><strong style="background-color:{{ $cellBg }}; color:{{ $textBg }}; border: 1px solid {{ $borderBg }}; padding: 3px 8px; border-radius: 6px;">{{ number_format($p->stock) }}</strong></td>
                    <td>@if($p->is_active)<span class="badge bg-success">Aktif</span>@else<span class="badge bg-secondary">Nonaktif</span>@endif</td>
                    <td><div class="d-flex gap-1">
                        <a href="{{ route('products.show',$p) }}" class="btn btn-sm btn-icon btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        @if(auth()->user()->role === 'pemilik')
                        <a href="{{ route('products.edit',$p) }}" class="btn btn-sm btn-icon btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('products.destroy',$p) }}" onsubmit="event.preventDefault(); Swal.fire({title: 'Hapus Produk?', text: 'Hapus produk ini?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'}).then(r => { if(r.isConfirmed) this.submit(); })">@csrf @method('DELETE')<button class="btn btn-sm btn-icon btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                        @endif
                    </div></td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center py-5 text-muted"><i class="bi bi-box-seam" style="font-size:2.5rem;display:block;margin-bottom:10px"></i>Tidak ada produk ditemukan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())<div class="card-body border-top py-3">{{ $products->withQueryString()->links() }}</div>@endif
</div>
</div><!-- Akhir Kontainer Hasil Produk -->
@endsection

@if(auth()->user()->role === 'pemilik')
<!-- Form Tersembunyi untuk Hapus Kategori & Satuan -->
<form id="deleteCategoryForm" method="POST" action="{{ route('products.category.destroy') }}">
    @csrf @method('DELETE')
    <input type="hidden" name="category" id="deleteCategoryValue">
</form>
<form id="deleteUnitForm" method="POST" action="{{ route('products.unit.destroy') }}">
    @csrf @method('DELETE')
    <input type="hidden" name="unit" id="deleteUnitValue">
</form>
@endif

@push('scripts')
<script>
// Fungsi inisialisasi pencarian AJAX
(function(){
    const si = document.getElementById('products-search');
    const f  = document.getElementById('products-filter-form');
    if(!si||!f) return;
    const base = '{{ route('products.index') }}';
    function params(q){ const d=new FormData(f); d.set('search',q); return new URLSearchParams(d).toString(); }
    async function go(q){ const url=base+'?'+params(q); history.replaceState(null,'',url); try{ const r=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}}); const html=await r.text(); const doc=new DOMParser().parseFromString(html,'text/html'); const p=doc.getElementById('products-results'); if(p) document.getElementById('products-results').innerHTML=p.innerHTML; }catch(e){ window.location.href=url; } }
    let t; si.addEventListener('input',function(){ clearTimeout(t); const q=this.value; t=setTimeout(()=>go(q),380); });
})();

// Fungsi untuk menghapus kategori terpilih
function deleteIndexCategory(){
    const select=document.getElementById('idxCategorySelect');
    const value=select?.value || '';
    if(!value){ Swal.fire({icon: 'warning', title: 'Perhatian', text: 'Pilih kategori yang ingin dihapus terlebih dahulu.'}); return; }
    Swal.fire({
        title: 'Hapus Kategori?',
        text: `Hapus kategori "${value}"? Produk yang memakai kategori ini akan dipindahkan ke LAIN-LAIN.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteCategoryValue').value=value;
            document.getElementById('deleteCategoryForm').submit();
        }
    });
}

// Fungsi untuk menghapus satuan terpilih
function deleteIndexUnit(){
    const select=document.getElementById('idxUnitSelect');
    const value=select?.value || '';
    if(!value){ Swal.fire({icon: 'warning', title: 'Perhatian', text: 'Pilih satuan yang ingin dihapus terlebih dahulu.'}); return; }
    Swal.fire({
        title: 'Hapus Satuan?',
        text: `Hapus satuan "${value}"? Produk yang memakai satuan ini akan dipindahkan ke PCS.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteUnitValue').value=value;
            document.getElementById('deleteUnitForm').submit();
        }
    });
}
</script>
@endpush
