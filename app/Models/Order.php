<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'code',
        'origin_name', 'origin_address', 'origin_lat', 'origin_lng',
        'destination_name', 'destination_address', 'destination_lat', 'destination_lng',
        'window_from', 'window_to',
        'weight', 'volume',
        'status', 'notes',
        // Ecommerce fields
        'total_amount',
        'customer_name',
        'customer_phone',
        'customer_email',
        'shipping_address',
        'payment_method',
        'payment_status',
        'validated_at',
        'validated_by',
        'assigned_driver_id',
        'assigned_at',
        'shipping_lat',
        'shipping_lng',
        'driver_accepted_at', 'driver_declined_at', 'driver_decline_reason',
        'driver_decline_admin_confirmed_at', 'driver_decline_admin_confirmed_by',
    ];

    protected $casts = [
        'window_from' => 'datetime',
        'window_to' => 'datetime',
        'origin_lat' => 'decimal:7',
        'origin_lng' => 'decimal:7',
        'destination_lat' => 'decimal:7',
        'destination_lng' => 'decimal:7',
        'total_amount' => 'decimal:2',
        'validated_at' => 'datetime',
        'assigned_at' => 'datetime',
        'shipping_lat' => 'decimal:8',
        'shipping_lng' => 'decimal:8',
        'driver_accepted_at' => 'datetime',
        'driver_declined_at' => 'datetime',
        'driver_decline_admin_confirmed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(OrderTrackingEvent::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'validated' => 'bg-blue-100 text-blue-800',
            'assigned' => 'bg-purple-100 text-purple-800',
            'in_transit' => 'bg-indigo-100 text-indigo-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function scopePendingValidation($query)
    {
        return $query->where('status', 'pending')->whereNull('validated_at');
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'validated')->whereNotNull('validated_at');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned')->whereNotNull('assigned_driver_id');
    }
}
