@extends('layouts.app')
@section('title','Edit Produk')
@section('page_title','Edit Produk')

@section('content')
<div class="page-hdr"><div class="page-hdr-left"><h1><i class="bi bi-pencil-square me-2" style="color:var(--primary)"></i>Edit Produk</h1></div><a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Kembali</a></div>
<form method="POST" action="{{ route('products.update',$product) }}">
@csrf @method('PUT')
<div class="card"><div class="card-body row g-3">
    <div class="col-md-3"><label class="form-label">Kode Produk</label><input type="text" name="product_code" class="form-control" value="{{ old('product_code',$product->product_code) }}" required><div class="form-text">Kode dibuat otomatis saat tambah produk, tetapi owner/admin masih bisa koreksi jika perlu.</div></div>
    <div class="col-md-5"><label class="form-label">Nama Produk *</label><input type="text" name="product_name" class="form-control" value="{{ old('product_name',$product->product_name) }}" required></div>
    <div class="col-md-2">
        <label class="form-label">Kategori *</label>
        <div class="input-group">
            <select name="category" id="categorySelect" class="form-select" required>
                <option value="">— Pilih —</option>
                @foreach($categories as $cat)<option value="{{ $cat }}" {{ old('category',$product->category)==$cat?'selected':'' }}>{{ $cat }}</option>@endforeach
            </select>
            <button type="button" class="btn btn-outline-primary" title="Tambah kategori" onclick="toggleNew('category')"><i class="bi bi-plus"></i></button>
            @if(auth()->user()->role === 'pemilik')
            <button type="button" class="btn btn-outline-danger" title="Hapus kategori terpilih" onclick="deleteSelectedCategory()"><i class="bi bi-trash"></i></button>
            @endif
        </div>
        <input type="text" name="new_category" id="new_category" class="form-control mt-2 d-none" placeholder="Kategori baru">
    </div>
    <div class="col-md-2">
        <label class="form-label">Satuan *</label>
        <div class="input-group">
            <select name="unit" id="unitSelect" class="form-select" required>
                <option value="">— Pilih —</option>
                @foreach($units as $unit)<option value="{{ strtoupper($unit) }}" {{ old('unit',strtoupper($product->unit))==strtoupper($unit)?'selected':'' }}>{{ strtoupper($unit) }}</option>@endforeach
            </select>
            <button type="button" class="btn btn-outline-primary" title="Tambah satuan" onclick="toggleNew('unit')"><i class="bi bi-plus"></i></button>
            @if(auth()->user()->role === 'pemilik')
            <button type="button" class="btn btn-outline-danger" title="Hapus satuan terpilih" onclick="deleteSelectedUnit()"><i class="bi bi-trash"></i></button>
            @endif
        </div>
        <input type="text" name="new_unit" id="new_unit" class="form-control mt-2 d-none" placeholder="Satuan baru" oninput="this.value=this.value.toUpperCase()">
    </div>
    <div class="col-md-3"><label class="form-label">HPP</label><div class="input-group"><span class="input-group-text">Rp</span><input type="number" name="hpp" id="hpp" class="form-control" value="{{ old('hpp',$product->hpp) }}" min="0" step="any" required oninput="calcSelling()"></div><div class="form-text">HPP akan otomatis diperbarui oleh Pembelian/Restock. Edit manual hanya untuk koreksi awal.</div></div>
    <div class="col-md-3"><label class="form-label">Markup %</label><div class="input-group"><input type="number" id="markup" class="form-control" value="0" min="0" step="0.1" oninput="calcSelling()"><span class="input-group-text">%</span></div></div>
    <div class="col-md-3"><label class="form-label">Harga Jual *</label><div class="input-group"><span class="input-group-text">Rp</span><input type="number" name="selling_price" id="selling_price" class="form-control" value="{{ old('selling_price',$product->selling_price) }}" min="0" step="any" required></div></div>
    <div class="col-md-3"><label class="form-label">Minimum Stok *</label><input type="number" name="minimum_stock" class="form-control" value="{{ old('minimum_stock',$product->minimum_stock) }}" min="0" required></div>
    <div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active',$product->is_active)?'checked':'' }}><label class="form-check-label">Produk aktif</label></div></div>
    <div class="col-12"><div class="alert alert-info mb-0">Supplier digunakan pada Pembelian/Restock, bukan di Master Produk.</div></div>
</div><div class="card-body border-top"><button class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Simpan Perubahan</button></div></div>
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
function toggleNew(type){ const el=document.getElementById('new_'+type); el.classList.toggle('d-none'); if(type==='unit') el.value=el.value.toUpperCase(); if(!el.classList.contains('d-none')) el.focus(); }
function calcSelling(){ const hpp=Number(document.getElementById('hpp').value||0); const markup=Number(document.getElementById('markup').value||0); if(hpp>0 && markup>0){ document.getElementById('selling_price').value=Math.ceil((hpp*(1+markup/100))/100)*100; } }
function deleteSelectedCategory(){
    const select=document.getElementById('categorySelect');
    const value=select?.value || '';
    if(!value){ alert('Pilih kategori yang ingin dihapus terlebih dahulu.'); return; }
    if(!confirm(`Hapus kategori "${value}"? Produk yang memakai kategori ini akan dipindahkan ke LAIN-LAIN.`)) return;
    document.getElementById('deleteCategoryValue').value=value;
    document.getElementById('deleteCategoryForm').submit();
}
function deleteSelectedUnit(){
    const select=document.getElementById('unitSelect');
    const value=select?.value || '';
    if(!value){ alert('Pilih satuan yang ingin dihapus terlebih dahulu.'); return; }
    if(!confirm(`Hapus satuan "${value}"? Produk yang memakai satuan ini akan dipindahkan ke PCS.`)) return;
    document.getElementById('deleteUnitValue').value=value;
    document.getElementById('deleteUnitForm').submit();
}
</script>
@endpush
