<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BlogView;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Helpers\StringHelper;

class AdminBlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::query()
            ->with(['author', 'category'])
            ->withCount('comments');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('judul', 'like', "%{$s}%")
                    ->orWhere('excerpt', 'like', "%{$s}%")
                    ->orWhere('isi', 'like', "%{$s}%");
            });
        }

        if ($cat = $request->get('kategori')) {
            $query->whereHas('category', fn($c) => $c->where('slug', $cat));
        }

        $hasLog = Schema::hasTable('blog_views');
        if ($hasLog) {
            $query->withCount('views');
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'popular':
                    if ($hasLog) {
                        $query->orderBy('views_count', 'desc');
                    } elseif (Schema::hasColumn('blog_posts', 'views_count')) {
                        $query->orderBy('views_count', 'desc');
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

        $todayViews = $hasLog ? BlogView::whereDate('viewed_at', now()->toDateString())->count() : 0;
        $monthViews = $hasLog ? BlogView::whereBetween('viewed_at', [now()->startOfMonth(), now()])->count() : 0;
        $yearViews  = $hasLog ? BlogView::whereBetween('viewed_at', [now()->startOfYear(), now()])->count() : 0;

        return view('admin.blog.index', compact('posts', 'todayViews', 'monthViews', 'yearViews'));
    }

    public function create()
    {
        return view('admin.blog.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'     => 'required|string|max:255',
            'isi'       => 'required',
            'excerpt'   => [
                'nullable', 'string', 'max:160',
                function ($attribute, $value, $fail) {
                    if ($value === null || $value === '') return;
                    if (preg_match('/\s/', $value)) {
                        $fail('Ringkasan (Excerpt) tidak boleh mengandung spasi sama sekali.');
                    }
                },
            ],
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            try {
                $filename = time() . '_' . Str::random(5) . '.webp';
                $manager  = new ImageManager(new Driver());
                $img      = $manager->decodePath($file->getRealPath());
                $img->scaleDown(width: 3840);
                $encoded  = $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 80));
                \Illuminate\Support\Facades\Storage::disk('public')->put('thumbnails/' . $filename, (string)$encoded);
                $thumbnailPath = 'thumbnails/' . $filename;
            } catch (\Exception $e) {
                $thumbnailPath = $file->store('thumbnails', 'public');
            }
        }

        $judul = StringHelper::censorProfanity($request->judul);
        $isi   = StringHelper::censorProfanity($request->isi);

        BlogPost::create([
            'judul'        => $judul,
            'slug'         => Str::slug($judul) . '-' . Str::random(6),
            'excerpt'      => $request->filled('excerpt') ? $request->excerpt : null,
            'isi'          => $isi,
            'thumbnail'    => $thumbnailPath,
            'user_id'      => auth()->id(),
            'published_at' => now(),
        ]);

        \Illuminate\Support\Facades\Cache::forget('home_latest_posts_v2');

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
            'judul'     => 'required|string|max:255',
            'isi'       => 'required',
            'excerpt'   => [
                'nullable', 'string', 'max:160',
                function ($attribute, $value, $fail) {
                    if ($value === null || $value === '') return;
                    if (preg_match('/\s/', $value)) {
                        $fail('Ringkasan (Excerpt) tidak boleh mengandung spasi sama sekali.');
                    }
                },
            ],
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $judul = StringHelper::censorProfanity($request->judul);
        $isi   = StringHelper::censorProfanity($request->isi);

        $data = [
            'judul'   => $judul,
            'isi'     => $isi,
            'excerpt' => $request->filled('excerpt') ? $request->excerpt : null,
        ];

        if ($blog->judul !== $judul) {
            $data['slug'] = Str::slug($judul) . '-' . Str::random(6);
        }

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            try {
                $filename = time() . '_' . Str::random(5) . '.webp';
                $manager  = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $img      = $manager->decodePath($file->getRealPath());
                $img->scaleDown(width: 3840);
                $encoded  = $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 80));
                \Illuminate\Support\Facades\Storage::disk('public')->put('thumbnails/' . $filename, (string)$encoded);
                $data['thumbnail'] = 'thumbnails/' . $filename;
            } catch (\Exception $e) {
                $data['thumbnail'] = $file->store('thumbnails', 'public');
            }
        }

        $blog->update($data);

        \Illuminate\Support\Facades\Cache::forget('home_latest_posts_v2');

        return redirect()->route('admin.blog.index')
            ->with('success', 'Artikel berhasil diperbarui!');
    }

    public function destroy(BlogPost $blog)
    {
        $blog->delete();
        \Illuminate\Support\Facades\Cache::forget('home_latest_posts_v2');
        return redirect()->route('admin.blog.index')
            ->with('success', 'Artikel berhasil dihapus!');
    }
}
