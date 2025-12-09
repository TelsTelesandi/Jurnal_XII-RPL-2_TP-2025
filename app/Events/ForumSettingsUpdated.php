<?php

namespace App\Events;

use App\Models\ForumSetting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ForumSettingsUpdated implements ShouldBroadcast
{
    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Create a new event instance.
     *
     * @param  array  $settings  Forum settings
     */
    /*******  77c31c3b-d014-47eb-9c11-838b8168e1af  *******/
    use SerializesModels;

    public ForumSetting $settings;

    public function __construct(ForumSetting $settings)
    {
        $this->settings = $settings;
    }

    public function broadcastOn(): array
    {
        return [new Channel('forum.settings')];
    }

    public function broadcastWith(): array
    {
        return [
            'settings' => [
                'allow_attachments' => $this->settings->allow_attachments,
                'allow_polls' => $this->settings->allow_polls,
                'allow_voice' => $this->settings->allow_voice,
                'max_attachment_kb' => $this->settings->max_attachment_kb,
                'default_poll_duration_minutes' => $this->settings->default_poll_duration_minutes,
                'max_users' => $this->settings->max_users,
            ]
        ];
    }
}
