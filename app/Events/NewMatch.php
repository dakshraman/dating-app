<?php

namespace App\Events;

use App\Models\UserMatch;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class NewMatch implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public UserMatch $match
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->match->user1_id}"),
            new PrivateChannel("user.{$this->match->user2_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'match_id' => $this->match->id,
            'matched_at' => $this->match->matched_at,
            'user1' => [
                'id' => $this->match->user1->id,
                'name' => $this->match->user1->name,
                'profile_photo' => $this->match->user1->profile_photo,
            ],
            'user2' => [
                'id' => $this->match->user2->id,
                'name' => $this->match->user2->name,
                'profile_photo' => $this->match->user2->profile_photo,
            ],
        ];
    }
}
