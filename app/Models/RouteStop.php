<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_plan_id', 'order_id', 'seq', 'lat', 'lng',
        'eta', 'etd', 'arrived_at', 'departed_at', 'status',
        'distance_from_prev_km', 'duration_from_prev_min',
    ];

    protected $casts = [
        'eta' => 'datetime',
        'etd' => 'datetime',
        'arrived_at' => 'datetime',
        'departed_at' => 'datetime',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'distance_from_prev_km' => 'decimal:2',
    ];

    public function routePlan(): BelongsTo
    {
        return $this->belongsTo(RoutePlan::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
