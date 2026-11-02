<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class LiveReaction extends Model
{
    use MassPrunable;

    protected $fillable = [
        'visitor',
        'reaction',
    ];

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        // Every visitor shares the live props demo, so it only keeps the last hour
        return static::query()->where('updated_at', '<=', now()->subHour());
    }
}
