@extends('layouts.app')
@section('title','Tambah Supplier')
@section('page_title','Tambah Supplier')

@section('content')
<!-- Header Halaman -->
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-plus-circle me-2" style="color:var(--primary)"></i>Tambah Supplier</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Supplier</a></li>
            <li class="breadcrumb-item active">Tambah</li>
        </ol></nav>
    </div>
</div>
<!-- Kontainer Utama -->
<div class="row justify-content-center">
    <!-- Kolom Tengah: Form Tambah Supplier -->
    <div class="col-12 col-lg-7">
        <!-- Kartu Form Tambah Supplier -->
        <div class="card">
            <div class="card-header"><h6>Form Supplier</h6></div>
            <div class="card-body">
                <!-- Form Tambah Supplier -->
                <form method="POST" action="{{ route('suppliers.store') }}">
                @csrf
                <input type="hidden" name="return_to" value="{{ $returnTo ?? request('return_to') }}">
                <div class="mb-3">
                    <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="Nama perusahaan / toko supplier" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Kontak Person <span class="text-danger">*</span></label>
                    <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person') }}" placeholder="Nama PIC supplier" required>
                    @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Alamat <span class="text-danger">*</span></label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3" placeholder="Alamat lengkap supplier" required>{{ old('address') }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-2"></i>Simpan</button>
                    <a href="{{ ($returnTo ?? request('return_to')) === 'purchases.create' ? route('purchases.create') : route('suppliers.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
