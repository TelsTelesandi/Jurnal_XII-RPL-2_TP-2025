<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ForumBan;

class EnsureNotBannedFromForum
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user)
            return $next($request);

        // cari ban aktif terbaru
        $ban = ForumBan::where('user_id', $user->id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('id')->first();

        if (!$ban)
            return $next($request);

        // paksa logout (hancurkan sesi + CSRF)
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // respon JSON dengan detail ban (untuk fetchBanAware)
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda telah diban dari forum',
                'ban' => [
                    'type' => $ban->ban_type === 'permanent' ? 'permanent' : 'temporary',
                    'expires_at' => optional($ban->expires_at)->toISOString(),
                    'expires_at_local' => optional($ban->expires_at)->format('Y-m-d H:i:s'),
                    'remaining_seconds' => $ban->expires_at ? now()->diffInSeconds($ban->expires_at, false) : null,
                ],
            ], 403);
        }

        // untuk request non-JSON → redirect dengan flash message
        return redirect()->route('home')->with('error', 'Anda telah diban dari forum.');
    }
}
