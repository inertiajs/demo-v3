<?php

use App\Models\User;

it('renders the swapping layers page', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/wizard');

    $page->assertSee('Swapping Layers')
        ->assertSee('A wizard in one modal')
        ->assertSee('Superseding a layer beneath')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertNoJavaScriptErrors();
});

it('walks the steps in one layer', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/wizard');

    $page->click('Start the wizard')
        ->waitForText('Who are we inviting?')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertPathIs('/features/layers/wizard/details')
        ->click('Next')
        ->waitForText('What may they see?')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertPathIs('/features/layers/wizard/access')
        ->click('Next')
        ->waitForText('Everything look right?')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertPathIs('/features/layers/wizard/review')
        ->assertNoJavaScriptErrors();
});

it('walks the steps in reverse with the back button', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/wizard/details');

    $page->waitForText('Who are we inviting?')
        ->click('Next')
        ->waitForText('What may they see?')
        ->back()
        ->waitForText('Who are we inviting?')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertPathIs('/features/layers/wizard/details')
        ->assertNoJavaScriptErrors();
});

it('stacks a differently keyed layer on top of the wizard', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/wizard/details');

    $page->waitForText('Who are we inviting?')
        ->click('What is a key?')
        ->waitForText('A layer of its own, stacked on the wizard.')
        ->assertCount('dialog[data-layer-index]', 2)
        ->assertPathIs('/features/layers/wizard/help')
        ->assertNoJavaScriptErrors();
});

it('rewrites the wizard beneath and closes the layer above it', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/wizard/details');

    $page->waitForText('Who are we inviting?')
        ->click('What is a key?')
        ->waitForText('A layer of its own, stacked on the wizard.')
        ->assertCount('dialog[data-layer-index]', 2)
        ->click('Jump ahead to review')
        ->waitForText('Everything look right?')
        ->wait(1)
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertPathIs('/features/layers/wizard/review')
        ->assertNoJavaScriptErrors();
});

it('transitions the layer alone when a step supersedes it', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/wizard/details');

    $page->waitForText('Who are we inviting?');

    $page->script('window.__vt = 0; const original = document.startViewTransition?.bind(document); if (original) { document.startViewTransition = (cb) => { window.__vt++; return original(cb); }; }');

    $page->click('Next')
        ->waitForText('What may they see?')
        ->assertScript('window.__vt > 0')
        ->assertScript('getComputedStyle(document.querySelector("[data-layer-id]")).viewTransitionName.startsWith("inertia-layer-")')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertNoJavaScriptErrors();
});

it('replaces the whole stack with an ordinary page response', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/wizard/details');

    $page->waitForText('Who are we inviting?')
        ->click('Leave the wizard')
        ->waitForText('Local Layers')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertPathIs('/features/layers/local')
        ->assertNoJavaScriptErrors();
});
