<?php

use App\Models\User;

it('renders the detours page with the gate locked', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/detours');

    $page->assertSee('Detours')
        ->assertSee('Walk the detour')
        ->assertSee('How the capture survives')
        ->assertSee('locked')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertNoJavaScriptErrors();
});

it('sends a guarded layer request to a full page prompt', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/detours');

    $page->click('Open the guarded layer')
        ->waitForText('Confirm to continue')
        ->assertPathIs('/features/layers/detours/confirm')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertNoJavaScriptErrors();
});

it('opens the layer over the page it was asked for from once the detour is done', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/detours');

    $page->click('Open the guarded layer')
        ->waitForText('Confirm to continue')
        ->click('Confirm and continue')
        ->waitForText('You came back here through the detour.')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertPathIs('/features/layers/detours/secret')
        ->assertSee('Walk the detour')
        ->assertNoJavaScriptErrors();
});

it('does not land back on the dead prompt', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/detours');

    $page->click('Open the guarded layer')
        ->waitForText('Confirm to continue')
        ->click('Confirm and continue')
        ->waitForText('You came back here through the detour.')
        ->back()
        ->wait(1)
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertPathIs('/features/layers/detours')
        ->assertNoJavaScriptErrors();
});

it('re-reads the gate whenever the page lands', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/detours');

    $page->click('Open the guarded layer')
        ->waitForText('Confirm to continue')
        ->click('Confirm and continue')
        ->waitForText('You came back here through the detour.')
        ->waitForText('unlocked')
        ->assertSee('Lock it again')
        ->assertNoJavaScriptErrors();
});

it('abandons a layer whose answer is an http error, leaving the page standing', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/detours');

    $page->click('Open a layer that 403s')
        ->waitForText('403: the open never landed')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertPathIs('/features/layers/detours')
        ->assertSee('Walk the detour')
        ->assertNoJavaScriptErrors();
});

it('replaces the whole stack when the error is left unhandled', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/detours');

    $page->uncheck('catch-it')
        ->click('Open a layer that 404s')
        ->waitForText("The page you're looking for doesn't exist.")
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertDontSee('Walk the detour')
        ->assertNoJavaScriptErrors();
});
