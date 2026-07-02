<?php

use App\Models\User;

it('renders the location event page', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/events/location-event');

    $page->assertSee('Location Event')
        ->assertSee('Simulate a Deployment')
        ->assertSee('Intercepting the Event')
        ->assertSee('Event Log')
        ->assertNoJavaScriptErrors();
});

it('intercepts a version change with a banner instead of reloading', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/events/location-event');

    $page->assertDontSee('A new version of this app is available')
        ->click('Deploy new version')
        ->assertSee('A new version of this app is available')
        ->assertSee('versionChange: true')
        ->assertNoJavaScriptErrors();
});
