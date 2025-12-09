<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // kalau ?refresh=1 => paksa hitung ulang (tidak pakai cache lama)
        $cacheKey = 'admin.dashboard.metrics.v2';
        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }

        // Cache ringan 5 detik (biar aman dari "nyangkut")
        $metrics = Cache::remember($cacheKey, 5, function () {

            $hasTable = fn(string $t) => Schema::hasTable($t);
            $hasColumn = fn(string $t, string $c) => $hasTable($t) && Schema::hasColumn($t, $c);

            // ===== USERS
            $usersTotal = $hasTable('users') ? (int) DB::table('users')->count() : 0;

            // ===== BLOG
            $articlesTotal = $hasTable('blog_posts') ? (int) DB::table('blog_posts')->count() : 0;

            // Komentar (di dump belum ada kolom moderasi → pakai total komentar)
            $commentsPending = $hasTable('blog_comments') ? (int) DB::table('blog_comments')->count() : 0;
            $commentsLabel = ($hasColumn('blog_comments', 'is_approved') || $hasColumn('blog_comments', 'approved_at') || $hasColumn('blog_comments', 'status'))
                ? 'Komentar Pending' : 'Total Komentar';

            // ===== FORUM
            // Topik = pesan root (reply_to_id NULL) & tidak dihapus kalau ada kolom is_deleted
            $topicsTotal = 0;
            if ($hasTable('forum_messages')) {
                $q = DB::table('forum_messages')->whereNull('reply_to_id');
                if ($hasColumn('forum_messages', 'is_deleted')) {
                    $q->where('is_deleted', 0);
                }
                $topicsTotal = (int) $q->count();
            }

            // Interaksi = total reactions; fallback → total pesan
            $forumInteractions = 0;
            if ($hasTable('forum_message_reactions')) {
                $forumInteractions = (int) DB::table('forum_message_reactions')->count();
            } elseif ($hasTable('forum_messages')) {
                $forumInteractions = (int) DB::table('forum_messages')->count();
            }

            // Yang ini kamu sembunyikan dulu di Blade, jadi biarkan 0
            $websiteViews = 0;
            $totalLogins = 0;
            $blogLogins = 0;
            $forumLogins = 0;

            return [
                'users_total' => $usersTotal,
                'articles_total' => $articlesTotal,
                'topics_total' => $topicsTotal,
                'comments_pending' => $commentsPending,
                'forum_interactions' => $forumInteractions,
                'blog_views' => 0,
                'website_views' => $websiteViews,
                'total_logins' => $totalLogins,
                'blog_logins' => $blogLogins,
                'forum_logins' => $forumLogins,
                'comments_label' => $commentsLabel,
            ];
        });

        return view('admin.dashboard', compact('metrics'));
    }
}
