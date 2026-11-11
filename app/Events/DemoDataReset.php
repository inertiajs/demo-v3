<?php

namespace App\Events;

use App\Models\Organization;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Inertia\Inertia;

class DemoDataReset implements ShouldBroadcastNow
{
    use Dispatchable;

    /**
     * Get when the demo data was last reset. Only `demo:reset` creates
     * organizations, so the oldest one marks the last reset.
     */
    public static function resetAt(): ?string
    {
        return Organization::min('created_at');
    }

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
            'demoResetAt' => static::resetAt(),
        ]);
    }
}
