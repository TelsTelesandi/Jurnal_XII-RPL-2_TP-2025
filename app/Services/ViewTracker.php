<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BlogView;
use Illuminate\Http\Request;

class ViewTracker
{
    const WINDOW_HOURS = 6;

    /**
     * Catat view unik. Return true jika view ini dihitung (unik), false jika terduplikasi.
     */
    public static function record(Request $request, BlogPost $post): bool
    {
        if (!$request->session()->isStarted()) {
            $request->session()->start();
        }
        $sessionId = $request->session()->getId();
        $userId = optional($request->user())->id;
        $ip = $request->ip();
        $agent = substr((string) $request->userAgent(), 0, 255);

        $exists = BlogView::where('blog_post_id', $post->id)
            ->where(function ($q) use ($userId, $sessionId, $ip) {
                $q->when($userId, fn($qq) => $qq->where('user_id', $userId))
                    ->orWhere('session_id', $sessionId)
                    ->orWhere('ip', $ip);
            })
            ->where('viewed_at', '>=', now()->subHours(self::WINDOW_HOURS))
            ->exists();

        if ($exists) {
            return false;
        }

        BlogView::create([
            'blog_post_id' => $post->id,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip' => $ip,
            'user_agent' => $agent,
            'viewed_at' => now(),
        ]);

        return true;
    }
}
