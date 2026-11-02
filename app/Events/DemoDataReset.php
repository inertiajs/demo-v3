<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Inertia\Inertia;

class DemoDataReset implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public ?string $resetAt = null) {}

    /**
     * Get the channel the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('demo');
    }

    /**
     * The reset time is the same for every visitor, so it travels with the
     * event and open pages learn about the reset without a request.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return Inertia::broadcastProps([
            'demoResetAt' => $this->resetAt,
        ]);
    }
}
