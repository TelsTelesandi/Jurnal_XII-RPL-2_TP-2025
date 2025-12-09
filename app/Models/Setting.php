<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings'; // pastikan sesuai

    // Mass assignment
    protected $fillable = [
        'key',
        'value',
    ];

    public $timestamps = false; // kalau tabel settings tidak punya created_at/updated_at
}
