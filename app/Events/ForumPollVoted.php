<?php
// app/Events/ForumPollVoted.php
namespace App\Events;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ForumPollVoted implements ShouldBroadcastNow
{
    use SerializesModels;
    public int $poll_id;
    public array $vote_counts;
    public int $total_votes;
    public function __construct(int $pollId, array $counts, int $total)
    {
        $this->poll_id = $pollId;
        $this->vote_counts = $counts;
        $this->total_votes = $total;
    }
    public function broadcastOn(): array
    {
        return [new PrivateChannel('forum.global')];
    }
    public function broadcastAs(): string
    {
        return 'forum.poll.voted';
    }
}
