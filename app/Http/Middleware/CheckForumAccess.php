<?php
// app/Http/Middleware/CheckForumAccess.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ForumSetting;

class CheckForumAccess
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Cek apakah user di-ban
        if ($user->isBannedFromForum()) {
            $ban = $user->activeBan;
            return response()->json([
                'status' => 'error',
                'message' => 'Anda telah di-ban dari forum',
                'ban_info' => [
                    'type' => $ban->ban_type,
                    'reason' => $ban->reason,
                    'expires_at' => $ban->expires_at,
                    'remaining_time' => $ban->remaining_time
                ]
            ], 403);
        }

        // Cek apakah forum terbuka (kecuali moderator/admin)
        $settings = ForumSetting::current();
        if (!$settings->is_open && !$user->canModerateForum()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forum sedang ditutup'
            ], 403);
        }

        return $next($request);
    }
}
