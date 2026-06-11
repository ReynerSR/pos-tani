@extends('layouts.app')
@section('title','Tambah Produk')
@section('page_title','Tambah Produk')

@section('content')
<div class="page-hdr"><div class="page-hdr-left"><h1><i class="bi bi-plus-square me-2" style="color:var(--primary)"></i>Tambah Produk</h1></div><a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Kembali</a></div>
<form method="POST" action="{{ route('products.store') }}">
@csrf
<div class="card"><div class="card-body row g-3">
    <div class="col-md-6"><label class="form-label">Nama Produk <span class="text-danger">*</span></label><input type="text" name="product_name" class="form-control" value="{{ old('product_name') }}" required></div>
    <div class="col-md-3">
        <label class="form-label">Kategori <span class="text-danger">*</span></label>
        <div class="input-group">
            <select name="category" id="categorySelect" class="form-select" {{ old('new_category') ? '' : 'required' }}>
                <option value="">— Pilih Kategori —</option>
                @foreach($categories as $cat)<option value="{{ $cat }}" {{ old('category')==$cat?'selected':'' }}>{{ $cat }}</option>@endforeach
            </select>
            <button type="button" class="btn btn-outline-primary" title="Tambah kategori" onclick="toggleNew('category')"><i class="bi bi-plus"></i></button>
            @if(auth()->user()->role === 'pemilik')
            <button type="button" class="btn btn-outline-danger" title="Hapus kategori terpilih" onclick="deleteSelectedCategory()"><i class="bi bi-trash"></i></button>
            @endif
        </div>
        <input type="text" name="new_category" id="new_category" class="form-control mt-2 {{ old('new_category') ? '' : 'd-none' }}" placeholder="Kategori baru" value="{{ old('new_category') }}" oninput="this.value=this.value.toUpperCase()">
        <div class="form-text">Kode produk otomatis dibuat dari kategori + ID produk. Hapus kategori akan memindahkan produknya ke LAIN-LAIN.</div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Satuan <span class="text-danger">*</span></label>
        <div class="input-group">
            <select name="unit" id="unitSelect" class="form-select" {{ old('new_unit') ? '' : 'required' }}>
                <option value="">— Pilih Satuan —</option>
                @foreach($units as $unit)<option value="{{ strtoupper($unit) }}" {{ old('unit')==strtoupper($unit)?'selected':'' }}>{{ strtoupper($unit) }}</option>@endforeach
            </select>
            <button type="button" class="btn btn-outline-primary" title="Tambah satuan" onclick="toggleNew('unit')"><i class="bi bi-plus"></i></button>
            @if(auth()->user()->role === 'pemilik')
            <button type="button" class="btn btn-outline-danger" title="Hapus satuan terpilih" onclick="deleteSelectedUnit()"><i class="bi bi-trash"></i></button>
            @endif
        </div>
        <input type="text" name="new_unit" id="new_unit" class="form-control mt-2 {{ old('new_unit') ? '' : 'd-none' }}" placeholder="Satuan baru, contoh: PACK" oninput="this.value=this.value.toUpperCase()" value="{{ old('new_unit') }}">
        <div class="form-text">Satuan otomatis disimpan uppercase. Hapus satuan akan memindahkan produknya ke PCS.</div>
    </div>
    @if(auth()->user()->role !== 'admin')
    <div class="col-md-3"><label class="form-label">HPP Awal</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" name="hpp" id="hpp" class="form-control rupiah-input" value="{{ old('hpp',0) }}" oninput="calcSelling()"></div><div class="form-text">Set 0 jika HPP akan dihitung dari pembelian/restock pertama.</div></div>
    <div class="col-md-3"><label class="form-label">Markup %</label><div class="input-group"><input type="number" id="markup" name="markup" class="form-control" value="{{ old('markup', session('_old_input.markup', 0)) }}" min="0" step="0.1" oninput="calcSelling()"><span class="input-group-text">%</span></div><div class="form-text">Opsional untuk bantu hitung harga jual dari HPP.</div></div>
    <div class="col-md-3"><label class="form-label">Harga Jual <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" name="selling_price" id="selling_price" class="form-control rupiah-input" value="{{ old('selling_price',0) }}" required></div><div class="form-text">Harga Jual.</div></div>
    @else
    <input type="hidden" name="hpp" value="0">
    <input type="hidden" name="markup" value="0">
    <input type="hidden" name="selling_price" value="0">
    @endif
    <div class="col-md-3"><label class="form-label">Minimum Stok <span class="text-danger">*</span></label><input type="number" name="minimum_stock" class="form-control" value="{{ old('minimum_stock',5) }}" min="0" required><div class="form-text">Pengingat Stok Kritis.</div></div>
    @if(auth()->user()->role === 'pemilik')
    <div class="col-md-3"><label class="form-label">Stok Awal Toko</label><input type="number" name="stock" class="form-control" value="{{ old('stock',0) }}" min="0"><div class="form-text">Stok tambahan lokasi lain dimasukkan lewat Pembelian/Restock atau Stock Opname.</div></div>
    @else
    <input type="hidden" name="stock" value="0">
    @endif
    <div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">Produk aktif</label></div></div>
    <div class="col-12"><div class="alert alert-info mb-0">Supplier tidak dipilih di Master Produk. Supplier digunakan saat modul Pembelian/Restock sesuai faktur supplier.</div></div>
</div><div class="card-body border-top"><button class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Simpan Produk</button></div></div>
</form>

@if(auth()->user()->role === 'pemilik')
<form id="deleteCategoryForm" method="POST" action="{{ route('products.category.destroy') }}" class="d-none">
    @csrf @method('DELETE')
    <input type="hidden" name="category" id="deleteCategoryValue">
</form>
<form id="deleteUnitForm" method="POST" action="{{ route('products.unit.destroy') }}" class="d-none">
    @csrf @method('DELETE')
    <input type="hidden" name="unit" id="deleteUnitValue">
</form>
@endif
@endsection
@push('scripts')
<script>
function toggleNew(type){ 
    const el=document.getElementById('new_'+type); 
    const sel=document.getElementById(type+'Select');
    el.classList.toggle('d-none'); 
    el.value=el.value.toUpperCase(); 
    if(!el.classList.contains('d-none')) {
        el.focus();
        sel.removeAttribute('required');
        sel.value = '';
    } else {
        sel.setAttribute('required','required');
        el.value = '';
    }
}
function calcSelling(){ const hppVal=document.getElementById('hpp').value.replace(/\./g,'')||0; const hpp=Number(hppVal); const markup=Number(document.getElementById('markup').value||0); if(hpp>0 && markup>0){ const selling = Math.ceil((hpp*(1+markup/100))/100)*100; const spEl = document.getElementById('selling_price'); spEl.value = selling.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); } }
function deleteSelectedCategory(){
    const select=document.getElementById('categorySelect');
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
function deleteSelectedUnit(){
    const select=document.getElementById('unitSelect');
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
