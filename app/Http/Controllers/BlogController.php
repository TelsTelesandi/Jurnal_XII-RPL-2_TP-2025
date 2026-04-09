<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogView;
use App\Models\BlogComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use App\Rules\NoProfanity;
use App\Helpers\StringHelper;
use Waad\ProfanityFilter\Facades\ProfanityFilter;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        return $this->list($request);
    }

    public function list(Request $request): View
    {
        $query = BlogPost::query()
            ->with(['author', 'category'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        // Search: ?q=
        if ($q = trim($request->get('q', ''))) {
            $query->where(function ($s) use ($q) {
                $s->where('judul', 'like', "%{$q}%")
                    ->orWhere('excerpt', 'like', "%{$q}%")
                    ->orWhere('isi', 'like', "%{$q}%");
            });
        }

        // Filter kategori: ?kategori=slug
        if ($cat = $request->get('kategori')) {
            $query->whereHas('category', fn($c) => $c->where('slug', $cat));
        }

        $query->withCount(['comments', 'likes']);

        // Sort: ?sort=terlama|populer|banyak-komentar|terbaru(default)
        switch ($request->get('sort')) {
            case 'terlama':
                $query->orderBy('published_at', 'asc');
                break;
            case 'populer':
                $query->orderBy('views_count', 'desc');
                break;
            case 'banyak-komentar':
                $query->orderBy('comments_count', 'desc');
                break;
            default:
                $query->orderBy('published_at', 'desc');
        }

        $posts = $query->paginate(6)->withQueryString();

        return view('sections.blog-list', compact('posts'));
    }

    public function show(string $slug): View
    {
        $post = BlogPost::with(['author', 'category'])
            ->with(['comments' => function($query) {
                $query->with('user')->latest();
            }])
            ->where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        // Catat view unik (throttle 6 jam per session+IP)
        if (!request()->session()->isStarted()) {
            request()->session()->start();
        }

        $sessionId = request()->session()->getId();
        $key = "pv:{$post->id}:{$sessionId}:" . request()->ip();

        Cache::remember($key, now()->addHours(6), function () use ($post) {
            BlogView::create([
                'blog_post_id' => $post->id,
                'user_id' => optional(Auth::user())->id,
                'session_id' => session()->getId(),
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'viewed_at' => now(),
            ]);

            // (Opsional) Increment views_count jika kolom ada
            if (Schema::hasColumn('blog_posts', 'views_count')) {
                $post->increment('views_count');
                Cache::forget('home_latest_posts_v2'); // Sinkronisasi cache beranda
            }

            return true;
        });

        $relatedPosts = BlogPost::where('id', '!=', $post->id)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(6, ['*'], 'page');

        return view('sections.blog-show', compact('post', 'relatedPosts'));
    }

   public function storeComment(Request $request, BlogPost $post)
{
    if (Auth::check() && Auth::user()->isBannedFromForum()) {
        return back()->with('error', 'Akun Anda telah diblokir. Silakan hubungi admin untuk mengajukan banding.');
    }

    $request->validate([
        'comment' => ['required', 'string', 'max:1000', new NoProfanity()],
    ], [
        'comment.required' => 'Komentar wajib diisi!',
        'comment.max' => 'Komentar maksimal 1000 karakter!',
    ]);

    // Censor profanity in comment
    $censoredComment = StringHelper::censorProfanity($request->input('comment'));

    // Create comment
    BlogComment::create([
        'blog_post_id' => $post->id,
        'user_id' => Auth::id(),
        'isi' => $censoredComment,
    ]);

    Cache::forget('home_latest_posts_v2'); // Sinkronisasi cache beranda

    return back()->with('success', 'Komentar berhasil ditambahkan!');
}

    // Toggle like postingan (seperti IG/TikTok)
    public function toggleLike(Request $request, BlogPost $post)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $like = \App\Models\BlogLike::where('user_id', $user->id)
            ->where('blog_post_id', $post->id)
            ->first();

        if ($like) {
            $like->delete();
            $status = 'unliked';
        } else {
            \App\Models\BlogLike::create([
                'user_id' => $user->id,
                'blog_post_id' => $post->id,
            ]);
            $status = 'liked';
        }

        $likesCount = \App\Models\BlogLike::where('blog_post_id', $post->id)->count();

        Cache::forget('home_latest_posts_v2'); // Sinkronisasi cache beranda

        return response()->json([
            'status' => $status,
            'likes_count' => $likesCount
        ]);
    }

    // Melaporkan komentar
    public function reportComment(Request $request, BlogComment $comment)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Perlindungan terhadap Admin & Moderator
        if ($comment->user && in_array($comment->user->role_id, [1, 3])) {
            return response()->json(['status' => 'error', 'message' => 'Tidak dapat melaporkan Admin atau Moderator.']);
        }

        $existing = \App\Models\BlogCommentReport::where('reporter_id', $user->id)
            ->where('comment_id', $comment->id)
            ->first();

        if ($existing) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah melaporkan komentar ini!']);
        }

        \App\Models\BlogCommentReport::create([
            'reporter_id' => $user->id,
            'comment_id' => $comment->id,
            'reason' => $request->reason,
        ]);

        $reportCount = \App\Models\BlogCommentReport::where('comment_id', $comment->id)->count();

        // Hapus komentar otomatis jika mencapai 3 laporan pada satu komentar
        if ($reportCount >= 3) {
            $comment->delete();
        }

        // Auto-Ban jika total laporan seumur hidup user ini mencapai 3
        if ($comment->user_id) {
            $targetUser = \App\Models\User::find($comment->user_id);
            if ($targetUser) {
                $totalUserReports = $targetUser->getTotalReportsCount();

                if ($totalUserReports >= 3) {
                    $alreadyBanned = \App\Models\ForumBan::where('user_id', $targetUser->id)
                        ->where('is_active', true)
                        ->whereNull('expires_at')
                        ->exists();

                    if (!$alreadyBanned) {
                        $systemAdminId = \App\Models\User::where('role_id', 1)->value('id') ?? 1;
                        \App\Models\ForumBan::create([
                            'user_id' => $targetUser->id,
                            'banned_by' => $systemAdminId,
                            'reason' => 'Auto-ban: Telah dilaporkan lebih dari 3 kali oleh pengguna lain.',
                            'is_active' => true,
                        ]);
                    }
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}