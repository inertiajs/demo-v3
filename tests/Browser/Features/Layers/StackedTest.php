<?php

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;

it('renders the stacked layers page', function () {
    $this->actingAs(User::factory()->create());
    Organization::factory()->has(Contact::factory(3))->create(['name' => 'Acme Corp']);

    $page = visit('/features/layers/stacked');

    $page->assertSee('Stacked Layers')
        ->assertSee('Start the stack')
        ->assertSee('Acme Corp')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertNoJavaScriptErrors();
});

it('stacks a modal on top of a slideover', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();
    $contact = Contact::factory()->create(['organization_id' => $organization->id]);

    $page = visit('/features/layers/stacked');

    $page->click('[data-test="open-organization"]')
        ->waitForText('Level 1 — a slideover.')
        ->click('[data-test="open-contact"]')
        ->waitForText('Level 2 — a modal on top of the slideover.')
        ->assertCount('dialog[data-layer-index]', 2)
        ->assertDataAttribute('dialog[data-layer-index="0"]', 'layer-top', 'false')
        ->assertDataAttribute('dialog[data-layer-index="1"]', 'layer-top', 'true')
        ->assertPathIs("/features/layers/stacked/{$organization->id}/{$contact->id}")
        ->assertNoJavaScriptErrors();
});

it('opens a local layer as a third level without moving the address', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();
    $contact = Contact::factory()->create(['organization_id' => $organization->id]);

    $page = visit("/features/layers/stacked/{$organization->id}/{$contact->id}");

    $page->waitForText('Level 2 — a modal on top of the slideover.')
        ->click('Add a note')
        ->waitForText('Level 3 — a local layer with no url of its own.')
        ->assertCount('dialog[data-layer-index]', 3)
        ->assertDataAttribute('dialog[data-layer-index="2"]', 'layer-type', 'local')
        ->assertPathIs("/features/layers/stacked/{$organization->id}/{$contact->id}")
        ->assertNoJavaScriptErrors();
});

it('closes only the top layer on escape', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();
    Contact::factory()->create(['organization_id' => $organization->id]);

    $page = visit('/features/layers/stacked');

    $page->click('[data-test="open-organization"]')
        ->waitForText('Level 1 — a slideover.')
        ->click('[data-test="open-contact"]')
        ->waitForText('Level 2 — a modal on top of the slideover.')
        ->keys('dialog[data-layer-index="1"]', 'Escape')
        ->wait(1)
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertSee('Level 1 — a slideover.')
        ->assertPathIs("/features/layers/stacked/{$organization->id}")
        ->assertNoJavaScriptErrors();
});

it('rewrites the layer beneath and closes the composer when a note is saved', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();
    $contact = Contact::factory()->create(['organization_id' => $organization->id]);

    $page = visit("/features/layers/stacked/{$organization->id}/{$contact->id}");

    $page->waitForText('Level 2 — a modal on top of the slideover.')
        ->click('Add a note')
        ->waitForText('Level 3 — a local layer with no url of its own.')
        ->type('dialog[data-layer-index="2"] textarea', 'Written from level three.')
        ->click('Save note')
        ->waitForText('Note added from a stacked layer.')
        ->assertCount('dialog[data-layer-index]', 2)
        ->assertSeeIn('dialog[data-layer-index="1"]', 'Written from level three.')
        ->assertNoJavaScriptErrors();
});

it('walks back down the stack one layer at a time', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();
    Contact::factory()->create(['organization_id' => $organization->id]);

    $page = visit('/features/layers/stacked');

    $page->click('[data-test="open-organization"]')
        ->waitForText('Level 1 — a slideover.')
        ->click('[data-test="open-contact"]')
        ->waitForText('Level 2 — a modal on top of the slideover.')
        ->back()
        ->wait(1)
        ->assertCount('dialog[data-layer-index]', 1)
        ->back()
        ->wait(1)
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertPathIs('/features/layers/stacked')
        ->assertNoJavaScriptErrors();
});

it('resolves a two deep layer url opened cold', function () {
    $this->actingAs(User::factory()->create());
    $organization = Organization::factory()->create();
    $contact = Contact::factory()->create(['organization_id' => $organization->id]);

    $page = visit("/features/layers/stacked/{$organization->id}/{$contact->id}");

    $page->waitForText('Level 2 — a modal on top of the slideover.')
        ->assertCount('dialog[data-layer-index]', 2)
        ->assertSee('Stacked Layers')
        ->assertNoJavaScriptErrors();
});
