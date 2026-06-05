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

            $timeoutMinutes = (int) config('session.lifetime', 30);
            $lastActivityAt = $request->session()->get('last_activity_at');

            if ($lastActivityAt && now()->diffInMinutes($lastActivityAt) >= $timeoutMinutes) {
                ActivityLog::record('AUTO_LOGOUT_TIMEOUT', "User {$user->username} otomatis logout karena tidak aktif {$timeoutMinutes} menit.");
                $user->forceFill(['last_seen_at' => null])->save();
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
