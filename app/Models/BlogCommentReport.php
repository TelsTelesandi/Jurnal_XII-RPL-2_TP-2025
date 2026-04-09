<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogCommentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'comment_id',
        'reason',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function comment()
    {
        return $this->belongsTo(BlogComment::class, 'comment_id');
    }
}
