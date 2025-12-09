<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{

    // app/Models/BlogPost.php (tambahkan)
    public function views()
    {
        return $this->hasMany(\App\Models\BlogView::class, 'blog_post_id');
    }

    protected $fillable = [
        'judul',
        'slug',
        'excerpt',
        'isi',
        'thumbnail',
        'user_id',
        'published_at',
        'status',
        'category_id'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // Relasi ke penulis
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke komentar
    public function comments()
    {
        return $this->hasMany(BlogComment::class);
    }

    // 🔑 Relasi ke kategori (wajib kalau mau pakai with('category'))
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
