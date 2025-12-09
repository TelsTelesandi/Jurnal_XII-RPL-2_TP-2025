<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogView extends Model
{
    protected $fillable = [
        'blog_post_id',
        'user_id',
        'session_id',
        'ip',
        'user_agent',
        'viewed_at'
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }
}
