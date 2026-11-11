<?php

use App\Models\User;

it('renders the global events page with event types', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/events/global-events');

    $page->assertSee('Global Events')
        ->assertSee('Trigger Events')
        ->assertSee('Available Events')
        ->assertSee('Event Log')
        ->assertNoJavaScriptErrors();
});

it('logs the navigate event type for initial loads, visits, and history navigation', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/events/global-events');

    $page->assertSee('navigate (initial)')
        ->click('Visit With Query')
        ->assertQueryStringHas('step', '2')
        ->assertSee('navigate (visit)')
        ->back()
        ->assertQueryStringMissing('step')
        ->assertSee('navigate (history)')
        ->assertNoJavaScriptErrors();
});
