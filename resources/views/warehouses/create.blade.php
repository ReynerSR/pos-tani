@extends('layouts.app')
@section('title','Tambah Tempat Penyimpanan')
@section('page_title','Tambah Tempat Penyimpanan')

@section('content')
<!-- Breadcrumb Navigasi -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('warehouses.index') }}">Master Gudang</a></li>
        <li class="breadcrumb-item active">Tambah Tempat Penyimpanan</li>
    </ol>
</nav>

<!-- Form Pembuatan Gudang Baru -->
<form method="POST" action="{{ route('warehouses.store') }}">
    @csrf
    <!-- Kartu Form Tambah Gudang -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-building me-2" style="color:#16a34a;"></i>Form Tambah Tempat Penyimpanan</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Kode Lokasi <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Contoh: GDG1" style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Alamat / Deskripsi</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="Alamat atau keterangan lokasi">
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-2 d-flex align-items-center gap-2">
                    <input type="hidden" name="is_store" value="0"><input type="checkbox" id="is_store" name="is_store" value="1" {{ old('is_store') ? 'checked' : '' }}>
                    <label for="is_store" class="mb-0">Jadikan Toko Utama</label>
                </div>
                <div class="col-md-2 d-flex align-items-center gap-2">
                    <input type="hidden" name="is_active" value="0"><input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label for="is_active" class="mb-0">Aktif</label>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan</button>
            <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
@endsection