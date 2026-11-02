<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Inertia\Inertia;

class ReactionAdded implements ShouldBroadcastNow
{
    use Dispatchable;

    /**
     * @param  array<string, int>  $reactions
     */
    public function __construct(
        public string $visitor = '',
        public string $reaction = '',
        public array $reactions = [],
    ) {}

    /**
     * Get the channel the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('live-props');
    }

    /**
     * Carry the new counts with the event. They are the same for every visitor,
     * so pages write them straight into the prop without making a request.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'visitor' => $this->visitor,
            'reaction' => $this->reaction,
            ...Inertia::broadcastProps([
                'reactions' => $this->reactions,
            ]),
        ];
    }
}
