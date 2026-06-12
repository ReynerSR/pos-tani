<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\ActivityLog;

class ProfileController extends Controller
{
    // Menampilkan halaman pengaturan profil pengguna yang sedang login
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    // Memperbarui data profil pengguna (nama, username, email, password)
    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'      => 'required|string|max:150',
            'username'  => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'     => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => 'nullable|string|min:6|confirmed',
        ]);

        // Enkripsi kata sandi jika diisi, jika kosong abaikan pembaruan kata sandi
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Simpan pembaruan data pengguna
        $user->update($data);
        
        // Catat aktivitas pembaruan profil
        ActivityLog::record('UPDATE_PROFILE', "Memperbarui profil sendiri.");

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
