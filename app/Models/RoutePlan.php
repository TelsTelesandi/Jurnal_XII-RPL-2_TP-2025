<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RoutePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'plan_date', 'total_distance_km', 'total_duration_min', 'status', 'notes',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'total_distance_km' => 'decimal:2',
    ];

    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(Assignment::class);
    }
}
