<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

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
        $user->forceFill(['last_seen_at' => now()])->save();

        ActivityLog::record('LOGIN', "Login berhasil — Role: {$user->role}");

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        ActivityLog::record('LOGOUT', 'Logout dari sistem');

        if (Auth::user()) {
            Auth::user()->forceFill(['last_seen_at' => null])->save();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
