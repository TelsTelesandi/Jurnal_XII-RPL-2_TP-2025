<?php
// app/Events/ForumToggled.php
namespace App\Events;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ForumToggled implements ShouldBroadcastNow
{
    use SerializesModels;
    public bool $is_open;
    public function __construct(bool $is_open)
    {
        $this->is_open = $is_open;
    }
    public function broadcastOn(): array
    {
        return [new PrivateChannel('forum.global')];
    }
    public function broadcastAs(): string
    {
        return 'forum.toggled';
    }
}
