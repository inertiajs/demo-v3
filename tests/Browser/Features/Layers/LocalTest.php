<?php

use App\Models\Contact;
use App\Models\User;

it('renders the local layers page', function () {
    $this->actingAs(User::factory()->create());
    Contact::factory(3)->create();

    $page = visit('/features/layers/local');

    $page->assertSee('Local Layers')
        ->assertSee('A confirmation prompt')
        ->assertSee('A picker that answers back')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertNoJavaScriptErrors();
});

it('opens a local layer without moving the address', function () {
    $this->actingAs(User::factory()->create());
    Contact::factory()->create(['is_favorite' => false]);

    $page = visit('/features/layers/local');

    $page->click('Add')
        ->waitForText('A local layer. Nothing was fetched to open it.')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertDataAttribute('dialog[data-layer-index]', 'layer-type', 'local')
        ->assertPathIs('/features/layers/local')
        ->assertNoJavaScriptErrors();
});

it('is a history step that back closes and forward reopens', function () {
    $this->actingAs(User::factory()->create());
    Contact::factory()->create(['is_favorite' => false]);

    $page = visit('/features/layers/local');

    $page->click('Add')
        ->waitForText('A local layer. Nothing was fetched to open it.')
        ->back()
        ->wait(1)
        ->assertNotPresent('dialog[data-layer-index]')
        ->forward()
        ->waitForText('A local layer. Nothing was fetched to open it.')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertNoJavaScriptErrors();
});

it('is dropped by a reload', function () {
    $this->actingAs(User::factory()->create());
    Contact::factory()->create(['is_favorite' => false]);

    $page = visit('/features/layers/local');

    $page->click('Add')
        ->waitForText('A local layer. Nothing was fetched to open it.')
        ->refresh()
        ->wait(1)
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertSee('Local Layers')
        ->assertNoJavaScriptErrors();
});

it('emits what the prompt decided back to the page that opened it', function () {
    $this->actingAs(User::factory()->create());
    $contact = Contact::factory()->create(['is_favorite' => false]);

    $page = visit('/features/layers/local');

    $page->click('Add')
        ->waitForText('A local layer. Nothing was fetched to open it.')
        ->click('Confirm')
        ->waitForText("confirmed for contact {$contact->id}")
        ->waitForText('the confirm layer closed')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertNoJavaScriptErrors();

    expect($contact->fresh()->is_favorite)->toBeTrue();
});

it('answers back from a picker rendered as a slideover', function () {
    $this->actingAs(User::factory()->create());
    Contact::factory()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);

    $page = visit('/features/layers/local');

    $page->click('Open the picker')
        ->waitForText('A local layer rendered as a slideover.')
        ->assertSeeIn('dialog[data-layer-index]', 'Ada Lovelace')
        ->click('dialog[data-layer-index] li button')
        ->waitForText('picked Ada Lovelace')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertNoJavaScriptErrors();
});
