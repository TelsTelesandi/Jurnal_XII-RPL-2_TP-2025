<?php
// app/Events/ForumMessageDeleted.php
namespace App\Events;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ForumMessageDeleted implements ShouldBroadcastNow
{
    use SerializesModels;
    public int $message_id;
    public function __construct(int $messageId)
    {
        $this->message_id = $messageId;
    }
    public function broadcastOn(): array
    {
        return [new PrivateChannel('forum.global')];
    }
    public function broadcastAs(): string
    {
        return 'forum.message.deleted';
    }
}
