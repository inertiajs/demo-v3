<?php

use App\Events\ReactionAdded;
use App\Events\VoteCast;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([VoteCast::class, ReactionAdded::class]);

    $this->actingAs(User::factory()->create());
});

it('counts one vote per visitor and moves it when they vote again', function () {
    $this->post(route('features.data-loading.live-props.votes'), ['framework' => 'vue'])->assertNoContent();
    $this->post(route('features.data-loading.live-props.votes'), ['framework' => 'vue'])->assertNoContent();
    $this->post(route('features.data-loading.live-props.votes'), ['framework' => 'react'])->assertNoContent();

    $this->get(route('features.data-loading.live-props'))
        ->assertInertia(fn ($page) => $page
            ->component('Features/DataLoading/LiveProps')
            ->where('votes', ['vue' => 0, 'react' => 1, 'svelte' => 0])
            ->where('yourVote', 'react')
        );

    Event::assertDispatchedTimes(VoteCast::class, 2);
    Event::assertDispatched(VoteCast::class, fn (VoteCast $event) => $event->framework === 'react' && str_starts_with($event->visitor, 'Visitor '));
});

it('gives every browser session its own vote', function () {
    $this->withSession(['live-props.visitor' => 'Visitor A'])
        ->post(route('features.data-loading.live-props.votes'), ['framework' => 'vue']);

    $this->withSession(['live-props.visitor' => 'Visitor B'])
        ->post(route('features.data-loading.live-props.votes'), ['framework' => 'svelte']);

    $this->get(route('features.data-loading.live-props'))
        ->assertInertia(fn ($page) => $page
            ->where('visitor', 'Visitor B')
            ->where('votes', ['vue' => 1, 'react' => 0, 'svelte' => 1])
            ->where('yourVote', 'svelte')
        );
});

it('broadcasts the new reaction counts with the event', function () {
    $this->post(route('features.data-loading.live-props.reactions'), ['reaction' => 'party'])->assertNoContent();

    Event::assertDispatched(ReactionAdded::class, fn (ReactionAdded $event) => $event->reaction === 'party'
        && $event->broadcastWith()['__inertia']['props']['reactions']['party'] === 1);
});

it('rejects unknown options', function () {
    $this->post(route('features.data-loading.live-props.votes'), ['framework' => 'angular'])->assertSessionHasErrors('framework');
    $this->post(route('features.data-loading.live-props.reactions'), ['reaction' => 'skull'])->assertSessionHasErrors('reaction');
});
