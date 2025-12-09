<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BlogView;
use Illuminate\Support\Facades\Schema;
use App\Helpers\StringHelper;

class AdminBlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::query()
            ->with(['author', 'category'])
            ->withCount('comments');

        // Search
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('judul', 'like', "%{$s}%")
                    ->orWhere('excerpt', 'like', "%{$s}%")
                    ->orWhere('isi', 'like', "%{$s}%");
            });
        }

        // Filter kategori
        if ($cat = $request->get('kategori')) {
            $query->whereHas('category', fn($c) => $c->where('slug', $cat));
        }

        // Aman: withCount('views') hanya jika tabel ada
        $hasLog = Schema::hasTable('blog_views');
        if ($hasLog) {
            $query->withCount('views'); // menghasilkan views_count dari relasi
        }

        // Sort
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'popular':
                    if ($hasLog) {
                        $query->orderBy('views_count', 'desc'); // dari withCount
                    } elseif (Schema::hasColumn('blog_posts', 'views_count')) {
                        $query->orderBy('views_count', 'desc'); // kolom denormalized (opsional)
                    } else {
                        $query->orderBy('created_at', 'desc');
                    }
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $posts = $query->paginate(10)->withQueryString();

        // Kartu statistik (butuh tabel blog_views)
        $todayViews = $hasLog ? BlogView::whereDate('viewed_at', now()->toDateString())->count() : 0;
        $monthViews = $hasLog ? BlogView::whereBetween('viewed_at', [now()->startOfMonth(), now()])->count() : 0;
        $yearViews = $hasLog ? BlogView::whereBetween('viewed_at', [now()->startOfYear(), now()])->count() : 0;

        return view('admin.blog.index', compact('posts', 'todayViews', 'monthViews', 'yearViews'));
    }

    public function create()
    {
        return view('admin.blog.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $thumbnailPath = $request->hasFile('thumbnail')
            ? $request->file('thumbnail')->store('thumbnails', 'public')
            : null;

        $judul = StringHelper::censorProfanity($request->judul);
        $isi = StringHelper::censorProfanity($request->isi);

        BlogPost::create([
            'judul' => $judul,
            'slug' => Str::slug($judul) . '-' . Str::random(6), // biar unik
            'excerpt' => Str::limit(strip_tags($isi), 160),
            'isi' => $isi,
            'thumbnail' => $thumbnailPath,
            'user_id' => auth()->id(),
            // 'published_at' => now(), // aktifkan kalau mau auto-publish
        ]);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Artikel berhasil dibuat!');
    }

    public function show($id)
    {
        $post = BlogPost::with(['author', 'comments.user', 'category'])->findOrFail($id);
        return view('admin.blog.show', compact('post'));
    }

    public function edit($id)
    {
        $post = BlogPost::findOrFail($id);
        return view('admin.blog.edit', compact('post'));
    }

    public function update(Request $request, BlogPost $blog)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $judul = StringHelper::censorProfanity($request->judul);
        $isi = StringHelper::censorProfanity($request->isi);

        $data = [
            'judul' => $judul,
            'isi' => $isi,
            'excerpt' => Str::limit(strip_tags($isi), 160),
        ];

        // perbarui slug kalau judul berubah (opsional)
        if ($blog->judul !== $judul) {
            $data['slug'] = Str::slug($judul) . '-' . Str::random(6);
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $blog->update($data);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Artikel berhasil diperbarui!');
    }

    public function destroy(BlogPost $blog)
    {
        $blog->delete();
        return redirect()->route('admin.blog.index')
            ->with('success', 'Artikel berhasil dihapus!');
    }
}
