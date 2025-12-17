<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate', 'name', 'capacity_weight', 'capacity_volume', 'active', 'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
