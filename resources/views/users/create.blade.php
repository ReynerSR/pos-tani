@extends('layouts.app')
@section('title','Tambah User')
@section('page_title','Tambah User')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-person-plus me-2" style="color:var(--primary)"></i>Tambah User</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Manajemen User</a></li>
            <li class="breadcrumb-item active">Tambah</li>
        </ol></nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><h6>Form Tambah Pengguna</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Nama lengkap pengguna" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username"
                               class="form-control @error('username') is-invalid @enderror"
                               value="{{ old('username') }}" placeholder="Unik, tanpa spasi" required>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email') }}" placeholder="Opsional">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimal 6 karakter" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', this)"><i class="bi bi-eye"></i></button>
                        </div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control" placeholder="Ulangi password" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation', this)"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required onchange="toggleMainOwner()">
                            @foreach($roles as $val => $label)
                            <option value="{{ $val }}" {{ old('role', 'kasir')==$val?'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end pb-1">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active',1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Akun Aktif</label>
                        </div>
                    </div>
                    @php
                        $hasMainOwner = \App\Models\User::where('is_main_owner', true)->exists();
                        $canSetMainOwner = !$hasMainOwner || auth()->user()->is_main_owner;
                    @endphp
                    <div class="col-12" id="mainOwnerDiv" style="display: {{ old('role') === 'pemilik' ? 'block' : 'none' }}; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #e2e8f0;">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_main_owner" id="is_main_owner" value="1" {{ old('is_main_owner') ? 'checked' : '' }} {{ !$canSetMainOwner ? 'disabled' : '' }}>
                            <label class="form-check-label" style="font-weight: 600; color:#1e40af;" for="is_main_owner">Jadikan Pemilik Toko Utama</label>
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-shield-lock me-1"></i>Hak khusus untuk menghapus user yang memiliki role Pemilik Toko.
                            @if(!$canSetMainOwner)
                            <span class="text-danger d-block mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Hanya Pemilik Toko Utama yang bisa memberikan status ini.</span>
                            @elseif($hasMainOwner)
                            <span class="text-warning d-block mt-1" style="color:#d97706!important"><i class="bi bi-info-circle me-1"></i>Perhatian: Hanya boleh ada 1 Pemilik Utama. Jika dicentang, status Anda sebagai Pemilik Utama akan <strong>dicabut</strong> dan dipindahkan.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 mt-3 mb-4" style="font-size:.82rem">
                    <i class="bi bi-shield-check me-2"></i>
                    <strong>Hak Akses:</strong>
                    Pemilik = full akses &amp; konfigurasi aturan |
                    Admin = back office &amp; laporan |
                    Kasir = transaksi POS
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-2"></i>Simpan User</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function togglePassword(id,btn){const input=document.getElementById(id); if(!input)return; input.type=input.type==='password'?'text':'password'; btn.innerHTML=input.type==='password'?'<i class="bi bi-eye"></i>':'<i class="bi bi-eye-slash"></i>';}
function toggleMainOwner(){
    const role = document.getElementById('role').value;
    const div = document.getElementById('mainOwnerDiv');
    if(div) {
        div.style.display = role === 'pemilik' ? 'block' : 'none';
        if(role !== 'pemilik') {
            document.getElementById('is_main_owner').checked = false;
        }
    }
}
// Run once on load to set initial state
document.addEventListener('DOMContentLoaded', toggleMainOwner);
</script>
@endpush
