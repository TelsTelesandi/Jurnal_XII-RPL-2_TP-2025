<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id', 'lat', 'lng', 'speed_kmh', 'heading_deg', 'accuracy_m', 'occurred_at', 'type',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }
}
