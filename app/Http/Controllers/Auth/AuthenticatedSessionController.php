<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    // Menampilkan halaman form login
    public function create()
    {
        return view('auth.login');
    }

    // Memproses permintaan login, validasi kredensial, dan manajemen sesi
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials, false)) {
            return back()->withErrors([
                'username' => 'Username atau password tidak sesuai.',
            ])->onlyInput('username');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors([
                'username' => 'Akun Anda telah dinonaktifkan. Hubungi pemilik toko.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('last_activity_at', now());
        
        // Simpan session ID terbaru di Cache untuk Anti-Multi Login
        \Illuminate\Support\Facades\Cache::forever('active_session_user_'.$user->id, session()->getId());

        $user->forceFill(['last_seen_at' => now()])->save();

        ActivityLog::record('LOGIN', "Login berhasil — Role: {$user->role}");

        return redirect()->intended(route('dashboard'));
    }

    // Memproses permintaan logout pengguna dari sistem dan menghancurkan sesi
    public function destroy(Request $request)
    {
        ActivityLog::record('LOGOUT', 'Logout dari sistem');

        if (Auth::user()) {
            Auth::user()->forceFill(['last_seen_at' => now()->subSeconds(0)])->save();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
