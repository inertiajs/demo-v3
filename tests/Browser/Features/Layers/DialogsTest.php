<?php

use App\Models\Contact;
use App\Models\User;

it('renders the modals and slideovers page', function () {
    $this->actingAs(User::factory()->create());
    Contact::factory(3)->create();

    $page = visit('/features/layers/dialogs');

    $page->assertSee('Modals & Slideovers')
        ->assertSee('Routed layers')
        ->assertSee('Opened from code')
        ->assertSee('Layer URLs are real URLs')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertNoJavaScriptErrors();
});

it('opens a routed layer over the page and takes the address', function () {
    $this->actingAs(User::factory()->create());
    $contact = Contact::factory()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);

    $page = visit('/features/layers/dialogs');

    $page->click('Modal')
        ->waitForText('A routed layer rendered as a modal.')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertPathIs("/features/layers/dialogs/{$contact->id}")
        ->assertAttribute('dialog[data-layer-index]', 'aria-label', 'Ada Lovelace')
        ->assertSee('Modals & Slideovers')
        ->assertNoJavaScriptErrors();
});

it('leaves the page beneath untouched while a layer opens and closes', function () {
    $this->actingAs(User::factory()->create());
    Contact::factory()->create();

    $page = visit('/features/layers/dialogs');

    $page->type('scratch', 'still here')
        ->click('Modal')
        ->waitForText('A routed layer rendered as a modal.')
        ->keys('dialog[data-layer-index]', 'Escape')
        ->wait(1)
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertPathIs('/features/layers/dialogs')
        ->assertValue('scratch', 'still here')
        ->assertNoJavaScriptErrors();
});

it('closes a layer with back and reopens it with forward', function () {
    $this->actingAs(User::factory()->create());
    $contact = Contact::factory()->create();

    $page = visit('/features/layers/dialogs');

    $page->click('Modal')
        ->waitForText('A routed layer rendered as a modal.')
        ->back()
        ->wait(1)
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertPathIs('/features/layers/dialogs')
        ->forward()
        ->waitForText('A routed layer rendered as a modal.')
        ->assertPathIs("/features/layers/dialogs/{$contact->id}")
        ->assertNoJavaScriptErrors();
});

it('opens a layer from code and reports the close on the handle', function () {
    $this->actingAs(User::factory()->create());
    Contact::factory()->create();

    $page = visit('/features/layers/dialogs');

    $page->click('Open modal')
        ->waitForText('A routed layer rendered as a modal.')
        ->keys('dialog[data-layer-index]', 'Escape')
        ->waitForText('modal closed')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertNoJavaScriptErrors();
});

it('renders a layer url opened cold with its base beneath it', function () {
    $this->actingAs(User::factory()->create());
    $contact = Contact::factory()->create();

    $page = visit("/features/layers/dialogs/{$contact->id}");

    $page->waitForText('A routed layer rendered as a modal.')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertSee('Modals & Slideovers')
        ->assertNoJavaScriptErrors();
});

it('applies an optimistic write to the layer and rolls it back on a refusal', function () {
    $this->actingAs(User::factory()->create());
    $contact = Contact::factory()->create(['is_favorite' => false]);

    $page = visit("/features/layers/dialogs/{$contact->id}");

    $page->waitForText('A routed layer rendered as a modal.')
        ->assertSeeIn('[data-test="favorite-state"]', 'no')
        ->click('Toggle')
        ->assertSeeIn('[data-test="favorite-state"]', 'yes')
        ->wait(2)
        ->assertSeeIn('[data-test="favorite-state"]', 'yes')
        ->assertNoJavaScriptErrors();

    expect($contact->fresh()->is_favorite)->toBeTrue();

    $page->check('refuse')
        ->click('Toggle')
        ->assertSeeIn('[data-test="favorite-state"]', 'no')
        ->waitForText('The server refused, so the optimistic change rolled back.')
        ->assertSeeIn('[data-test="favorite-state"]', 'yes')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertNoJavaScriptErrors();

    expect($contact->fresh()->is_favorite)->toBeTrue();
});

it('lands validation errors from a layer form on the layer', function () {
    $this->actingAs(User::factory()->create());
    $contact = Contact::factory()->create();

    $page = visit("/features/layers/dialogs/{$contact->id}/edit");

    $page->waitForText('A routed layer rendered as a slideover.')
        ->clear('first_name')
        ->click('Save')
        ->waitForText('The first name field is required.')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertSeeIn('dialog[data-layer-index]', 'The first name field is required.')
        ->assertNoJavaScriptErrors();
});

it('lands validation errors from a local layer form on that layer', function () {
    $this->actingAs(User::factory()->create());
    $contact = Contact::factory()->create();

    $page = visit("/features/layers/dialogs/{$contact->id}");

    $page->waitForText('A routed layer rendered as a modal.')
        ->click('Add a note')
        ->waitForText('A note about')
        ->assertCount('dialog[data-layer-index]', 2)
        ->click('Save note')
        ->waitForText('The body field is required.')
        ->assertCount('dialog[data-layer-index]', 2)
        ->assertSeeIn('dialog[data-layer-index="1"]', 'The body field is required.')
        ->assertNoJavaScriptErrors();

    $page->fill('body', 'Reviewed the account together.')
        ->click('Save note')
        ->waitForText('Reviewed the account together.')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertNoJavaScriptErrors();
});

it('closes the layer and refreshes the page beneath on a successful save', function () {
    $this->actingAs(User::factory()->create());
    $contact = Contact::factory()->create(['first_name' => 'Ada']);

    $page = visit("/features/layers/dialogs/{$contact->id}/edit");

    $page->waitForText('A routed layer rendered as a slideover.')
        ->fill('first_name', 'Grace')
        ->click('Save')
        ->waitForText('Grace')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertPathIs('/features/layers/dialogs')
        ->assertNoJavaScriptErrors();
});
