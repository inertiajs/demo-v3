<?php

use App\Events\CounterTicked;
use App\Events\ReactionAdded;
use App\Events\VoteCast;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Sleep;

it('renders the live props page', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/data-loading/live-props');

    $page->assertSee('Live Props')
        ->assertSee('Shared Poll')
        ->assertSee('v3.11')
        ->assertSee('Broadcast Values')
        ->assertSee('Throttling')
        ->assertSee('Live Events')
        ->assertSee('Event Log')
        ->assertNoJavaScriptErrors();
});

it('broadcasts votes, reactions and ticks', function () {
    Event::fake([VoteCast::class, ReactionAdded::class, CounterTicked::class]);
    Sleep::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/features/data-loading/live-props');

    $page->click('Vote Vue')
        ->click('Send 20 Events')
        ->click('[data-test="reactions-thumbsUp"]')
        ->wait(1)
        ->assertNoJavaScriptErrors();

    Event::assertDispatched(VoteCast::class);
    Event::assertDispatched(ReactionAdded::class, fn (ReactionAdded $event) => $event->reactions['thumbsUp'] === 1);
    Event::assertDispatched(CounterTicked::class);
});
