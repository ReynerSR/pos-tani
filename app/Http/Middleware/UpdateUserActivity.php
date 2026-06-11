<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            if (! $user->is_active) {
                ActivityLog::record('AUTO_LOGOUT_INACTIVE_USER', "Akun {$user->username} sudah dinonaktifkan dan dipaksa logout.");
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan. Hubungi pemilik toko.');
            }

            $activeSessionId = \Illuminate\Support\Facades\Cache::get('active_session_user_'.$user->id);
            if ($activeSessionId && $activeSessionId !== session()->getId()) {
                ActivityLog::record('AUTO_LOGOUT_MULTI_LOGIN', "Akun {$user->username} dilogout otomatis karena login di perangkat/browser lain.");
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Sesi berakhir karena akun Anda login di perangkat atau browser lain.');
            }

            $timeoutMinutes = (int) env('AUTH_TIMEOUT', 15);
            $lastActivityAt = $request->session()->get('last_activity_at');

            if ($lastActivityAt && now()->diffInMinutes($lastActivityAt) >= $timeoutMinutes) {
                ActivityLog::record('AUTO_LOGOUT_TIMEOUT', "User {$user->username} otomatis logout karena tidak aktif {$timeoutMinutes} menit.");
                $user->forceFill(['last_seen_at' => now()->subMinutes($timeoutMinutes + 1)])->save();
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', "Sesi berakhir karena tidak aktif selama {$timeoutMinutes} menit.");
            }

            $request->session()->put('last_activity_at', now());
            $user->forceFill(['last_seen_at' => now()])->save();
        }

        return $next($request);
    }
}
