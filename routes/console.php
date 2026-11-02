<?php

use App\Events\DemoDataReset;
use App\Models\Contact;
use App\Models\LiveReaction;
use App\Models\LiveTick;
use App\Models\LiveVote;
use App\Models\Note;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Replaces the demo data without touching the shared login or anyone's session,
// unlike migrate:fresh, which is also prohibited in production
Artisan::command('demo:reset', function () {
    DB::transaction(function () {
        Note::query()->delete();
        Contact::query()->delete();
        Organization::query()->delete();
        LiveVote::query()->delete();
        LiveReaction::query()->delete();
        LiveTick::query()->delete();

        User::where('email', '!=', UserSeeder::EMAIL)->delete();

        $this->call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);
    });

    broadcast(new DemoDataReset);
})->purpose('Replace the demo data, keeping the demo user signed in')->daily();

// The live props demo is shared by every visitor, so it only keeps the last hour
Schedule::command('model:prune', [
    '--model' => [LiveVote::class, LiveReaction::class, LiveTick::class],
])->hourly();
