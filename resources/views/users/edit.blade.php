@extends('layouts.app')
@section('title','Edit User')
@section('page_title','Edit User')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-pencil-square me-2" style="color:var(--primary)"></i>Edit User</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Manajemen User</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><h6>Edit Pengguna: {{ $user->name }}</h6></div>
            <div class="card-body">
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
                            <div class="mb-2" style="font-size:.8rem;color:#92400e">Password tersimpan: <strong>{{ $user->visible_password ?: 'Password lama tidak tersedia; isi password baru untuk menyimpan password tampil' }}</strong></div>
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
                        <select name="role" class="form-select" required>
                            @foreach($roles as $val => $label)
                            <option value="{{ $val }}" {{ old('role',$user->role)==$val?'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end pb-1">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active',$user->is_active) ? 'checked' : '' }}
                                   {{ ($user->id === auth()->id() || $user->role === 'pemilik') ? 'disabled' : '' }}>
                            <label class="form-check-label" for="is_active">Akun Aktif</label>
                            @if($user->id === auth()->id())
                            <div class="form-text">{{ $user->role === 'pemilik' ? 'Akun owner/pemilik tidak boleh dinonaktifkan' : 'Tidak dapat menonaktifkan akun sendiri' }}</div>
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
<script>function togglePassword(id,btn){const input=document.getElementById(id); if(!input)return; input.type=input.type==='password'?'text':'password'; btn.innerHTML=input.type==='password'?'<i class="bi bi-eye"></i>':'<i class="bi bi-eye-slash"></i>';}</script>
@endpush
