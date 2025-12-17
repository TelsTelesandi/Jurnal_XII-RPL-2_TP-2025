<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'delivered_at', 'recipient', 'signature_path', 'photo_path', 'status', 'notes',
        'driver_message', 'customer_confirmed_at', 'customer_confirmation_status', 'customer_notes',
        'customer_rating', 'customer_quality',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'customer_confirmed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
