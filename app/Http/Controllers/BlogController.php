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

        $query->withCount('comments');

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
            }

            return true;
        });

        $relatedPosts = BlogPost::where('id', '!=', $post->id)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(4)
            ->get();

        return view('sections.blog-show', compact('post', 'relatedPosts'));
    }

   public function storeComment(Request $request, BlogPost $post)
{
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

    return back()->with('success', 'Komentar berhasil ditambahkan!');
}
}