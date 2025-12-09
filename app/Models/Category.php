<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['nama', 'slug'];

    public function posts()
    {
        return $this->hasMany(BlogPost::class);
    }
}
