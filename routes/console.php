<?php

use App\Models\LiveReaction;
use App\Models\LiveTick;
use App\Models\LiveVote;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('migrate:fresh --seed --force')->daily();

// The live props demo is shared by every visitor, so it only keeps the last hour
Schedule::command('model:prune', [
    '--model' => [LiveVote::class, LiveReaction::class, LiveTick::class],
])->hourly();
