<?php

// app/Models/ForumPoll.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumPoll extends Model
{
    protected $fillable = [
        'message_id',
        'question',
        'options',
        'multiple_choice',
        'expires_at'
    ];

    protected $casts = [
        'options' => 'array',
        'multiple_choice' => 'boolean',
        'expires_at' => 'datetime'
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(ForumMessage::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ForumPollVote::class, 'poll_id');
    }

    public function getVoteCountsAttribute(): array
    {
        $votes = $this->votes()->get();
        $counts = array_fill(0, count($this->options), 0);

        foreach ($votes as $vote) {
            if (isset($counts[$vote->option_index])) {
                $counts[$vote->option_index]++;
            }
        }

        return $counts;
    }

    public function getTotalVotesAttribute(): int
    {
        return $this->votes()->distinct('user_id')->count();
    }

    public function hasUserVoted(User $user): bool
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }

    public function getUserVotes(User $user): array
    {
        return $this->votes()
            ->where('user_id', $user->id)
            ->pluck('option_index')
            ->toArray();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}