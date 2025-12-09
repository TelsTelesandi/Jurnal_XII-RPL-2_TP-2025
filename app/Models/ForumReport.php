<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumReport extends Model
{
    protected $table = 'forum_reports';

    protected $fillable = [
        'reporter_id',
        'target_user_id',
        'message_id',
        'reason',
        'notes',
        'ip',
        'user_agent',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(ForumMessage::class, 'message_id');
    }
}

