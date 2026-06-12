@extends('layouts.app')
@section('title','Edit User')
@section('page_title','Edit User')

@section('content')
<!-- Header Halaman -->
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-pencil-square me-2" style="color:var(--primary)"></i>Edit User</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Manajemen User</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
    </div>
</div>

<!-- Kontainer Utama -->
<div class="row justify-content-center">
    <!-- Kolom Tengah: Form Edit Pengguna -->
    <div class="col-12 col-lg-7">
        <!-- Kartu Form Edit Pengguna -->
        <div class="card">
            <div class="card-header"><h6>Edit Pengguna: {{ $user->name }}</h6></div>
            <div class="card-body">
                <!-- Form Edit Pengguna -->
                <form method="POST" action="{{ route('users.update',$user) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name',$user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username"
                               class="form-control @error('username') is-invalid @enderror"
                               value="{{ old('username',$user->username) }}" required>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email',$user->email) }}">
                    </div>

                    <div class="col-12">
                        <div class="p-3" style="background:#fffbeb;border-radius:10px;border:1px solid #fde68a">
                            <div style="font-size:.82rem;font-weight:600;color:#854d0e;margin-bottom:10px">
                                <i class="bi bi-lock me-1"></i>Ganti Password (kosongkan jika tidak ingin diubah)
                            </div>
                            <div class="mb-2" style="font-size:.8rem;color:#92400e"><strong>Isi password baru untuk mengganti password</strong></div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="password" name="password" id="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               placeholder="Password baru (min. 6 karakter)">
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', this)"><i class="bi bi-eye"></i></button>
                                    </div>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                               class="form-control" placeholder="Konfirmasi password baru">
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation', this)"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select" required onchange="toggleMainOwner()">
                            @foreach($roles as $val => $label)
                            <option value="{{ $val }}" {{ old('role',$user->role)==$val?'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @php
                        $canDisable = $user->id !== auth()->id() && ($user->role !== 'pemilik' || (auth()->user()->is_main_owner && !$user->is_main_owner));
                    @endphp
                    <div class="col-md-6 d-flex align-items-end pb-1">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active',$user->is_active) ? 'checked' : '' }}
                                   {{ !$canDisable ? 'disabled' : '' }}>
                            <label class="form-check-label" for="is_active">Akses Akun (Diizinkan)</label>
                            @if(!$canDisable)
                            <div class="form-text text-danger" style="font-size:0.75rem">
                                {{ $user->id === auth()->id() ? 'Tidak dapat menonaktifkan akun Anda sendiri.' : ($user->is_main_owner ? 'Akun Pemilik Toko Utama tidak dapat dinonaktifkan.' : 'Hanya Pemilik Toko Utama yang bisa menonaktifkan Pemilik Toko lain.') }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @php
                        $hasMainOwner = \App\Models\User::where('is_main_owner', true)->exists();
                        $canSetMainOwner = !$hasMainOwner || auth()->user()->is_main_owner;
                    @endphp
                    <div class="col-12" id="mainOwnerDiv" style="display: {{ old('role', $user->role) === 'pemilik' ? 'block' : 'none' }}; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #e2e8f0;">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_main_owner" id="is_main_owner" value="1" {{ old('is_main_owner', $user->is_main_owner) ? 'checked' : '' }} {{ !$canSetMainOwner ? 'disabled' : '' }}>
                            <label class="form-check-label" style="font-weight: 600; color:#1e40af;" for="is_main_owner">Jadikan Pemilik Toko Utama</label>
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-shield-lock me-1"></i>Hak khusus untuk menghapus user yang memiliki role Pemilik Toko.
                            @if(!$canSetMainOwner)
                            <span class="text-danger d-block mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Hanya Pemilik Toko Utama yang bisa memberikan status ini.</span>
                            @elseif($hasMainOwner && $user->id !== auth()->id() && !$user->is_main_owner)
                            <span class="text-warning d-block mt-1" style="color:#d97706!important"><i class="bi bi-info-circle me-1"></i>Perhatian: Hanya boleh ada 1 Pemilik Utama. Jika dicentang, status Anda sebagai Pemilik Utama akan <strong>dicabut</strong> dan dipindahkan ke pengguna ini.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-2"></i>Simpan Perubahan</button>
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
// Fungsi untuk melihat/menyembunyikan teks password
function togglePassword(id,btn){const input=document.getElementById(id); if(!input)return; input.type=input.type==='password'?'text':'password'; btn.innerHTML=input.type==='password'?'<i class="bi bi-eye"></i>':'<i class="bi bi-eye-slash"></i>';}
// Fungsi untuk menampilkan/menyembunyikan opsi "Pemilik Utama" berdasarkan role
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
// Jalankan fungsi saat halaman dimuat untuk mengatur status awal elemen
document.addEventListener('DOMContentLoaded', toggleMainOwner);
</script>
@endpush
