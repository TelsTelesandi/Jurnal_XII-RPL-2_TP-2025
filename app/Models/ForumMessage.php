<?php
// app/Models/ForumMessage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\MessageReaction;
use Illuminate\Support\Facades\Storage;


class ForumMessage extends Model
{



    protected $fillable = [
        'user_id',
        'message',
        'message_type',
        'metadata',
        'attachment',
        'reply_to_id',
        'is_deleted',
        'deleted_by',
        'deleted_at',
        'attachment_url',
        'formatted_created_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime'
    ];

    protected $with = ['user', 'replyTo', 'reactions']; // kalau mau reaksi ikut terkirim
    protected $appends = ['attachment_url', 'formatted_created_at'];

    /*************  ✨ Windsurf Command 🌟  *************/
    /**
     * @return HasMany
     */
    public function reactions(): HasMany
    {
        // Nggak usah pakai ->select(...) dulu; kalau nanti mau select, pastikan 'message_id' ikut
        // https://laravel.com/docs/8.x/eloquent-relationships#eager-loading
        // minimal: jangan pakai ->select(...) dulu; kalau nanti mau select, pastikan 'message_id' ikut
        return $this->hasMany(MessageReaction::class, 'message_id', 'id');
    }
    /*******  72aa08ec-d7d8-431a-9ecc-94739a43d330  *******/

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(ForumMessage::class, 'reply_to_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ForumMessage::class, 'reply_to_id')
            ->where('is_deleted', false)
            ->orderBy('created_at', 'asc');
    }

    public function poll(): HasOne
    {
        return $this->hasOne(ForumPoll::class, 'message_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /*************  ✨ Windsurf Command 🌟  *************/
    // Scopes
    /**
     * Scope a query to only include active messages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
    /*******  1f06ff13-bf05-4cfb-90c4-807171d341d0  *******/

    public function scopeByType($query, $type)
    {
        return $query->where('message_type', $type);
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', Carbon::now()->subHours($hours));
    }

    // Methods
    public function canBeDeletedBy(User $user): bool
    {
        return $user->id === $this->user_id ||
            $user->role_id === 1 || // Admin
            $user->role_id === 3;   // Moderator
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }



    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment) {
            return null;
        }

        // Gunakan Laravel helper `url()` agar hasil absolut,
        // contoh: http://localhost/storage/forum/xxx.pptx
        return url('storage/' . ltrim($this->attachment, '/'));
    }

}