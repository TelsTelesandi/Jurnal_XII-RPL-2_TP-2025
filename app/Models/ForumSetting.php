<?php

// app/Models/ForumSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ForumSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_open',
        'allow_attachments',
        'allow_polls',
        'allow_voice',
        'max_attachment_kb',
        'default_poll_duration_minutes',
        'max_users',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'allow_attachments' => 'boolean',
        'allow_polls' => 'boolean',
        'allow_voice' => 'boolean',
    ];

    public static function current(): ?self
    {
        return self::first(); // Asumsi single record
    }

    // Accessor untuk max_attachment_mb
    public function getMaxAttachmentMbAttribute(): ?float
    {
        return $this->max_attachment_kb ? round($this->max_attachment_kb / 1024, 2) : null;
    }

    // Accessor untuk default_poll_duration_formatted
    public function getDefaultPollDurationFormattedAttribute(): array
    {
        $minutes = $this->default_poll_duration_minutes ?? 0;
        if ($minutes === 0) {
            return ['value' => '', 'unit' => 'minutes'];
        }

        if ($minutes % 1440 === 0) {
            return ['value' => $minutes / 1440, 'unit' => 'days'];
        } elseif ($minutes % 60 === 0) {
            return ['value' => $minutes / 60, 'unit' => 'hours'];
        } else {
            return ['value' => $minutes, 'unit' => 'minutes'];
        }
    }
}