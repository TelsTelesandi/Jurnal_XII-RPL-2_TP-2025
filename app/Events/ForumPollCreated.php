<?php
// app/Events/ForumPollCreated.php
namespace App\Events;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ForumPollCreated implements ShouldBroadcastNow
{
    use SerializesModels;
    public array $message;
    public function __construct(array $message)
    {
        $this->message = $message;
    }
    public function broadcastOn(): array
    {
        return [new PrivateChannel('forum.global')];
    }
    public function broadcastAs(): string
    {
        return 'forum.poll.created';
    }
}
