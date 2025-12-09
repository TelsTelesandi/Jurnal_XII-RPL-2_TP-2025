<?php
// app/Events/ForumMessageCreated.php
namespace App\Events;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ForumMessageCreated implements ShouldBroadcastNow
{
    use SerializesModels;
    public array $message;
    public function __construct(array $messagePayload)
    {
        $this->message = $messagePayload;
    }
    public function broadcastOn(): array
    {
        return [new PrivateChannel('forum.global')];
    }
    public function broadcastAs(): string
    {
        return 'forum.message.created';
    }
}
