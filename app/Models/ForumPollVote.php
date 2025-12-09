<?php
//app/Models/ForumPollVote.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumPollVote extends Model
{
    protected $fillable = [
        'poll_id',
        'user_id',
        'option_index'
    ];

    /*************  ✨ Windsurf Command 🌟  *************/
    /**
     * Belongs to relationship to ForumPoll model
     *
     * @return BelongsTo
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(ForumPoll::class);
    }
    /*******  c0027950-b67b-4f41-bd32-dfe5fe4d0505  *******/

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}