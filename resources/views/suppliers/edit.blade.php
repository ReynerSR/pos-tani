@extends('layouts.app')
@section('title','Edit Supplier')
@section('page_title','Edit Supplier')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-pencil-square me-2" style="color:var(--primary)"></i>Edit Supplier</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Supplier</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><h6>Edit Supplier: {{ $supplier->name }}</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('suppliers.update',$supplier) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name',$supplier->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Kontak Person <span class="text-danger">*</span></label>
                    <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person',$supplier->contact_person) }}" required>
                    @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone',$supplier->phone) }}" required>
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Alamat <span class="text-danger">*</span></label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3" required>{{ old('address',$supplier->address) }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-2"></i>Simpan Perubahan</button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
