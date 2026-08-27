<?php

use App\Models\Contact;
use App\Models\User;

it('renders the data in a layer page', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/features');

    $page->assertSee('Data in a Layer')
        ->assertSee('Open the panel')
        ->assertSee('This page must not move')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertNoJavaScriptErrors();
});

it('opens the panel with every data feature inside it', function () {
    $this->actingAs(User::factory()->create());
    Contact::factory(12)->create();

    $page = visit('/features/layers/features');

    $page->click('Open')
        ->waitForText('Every request this layer makes targets the layer, not the page beneath.')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertSee('Layer refreshed at')
        ->assertSee('Merged props')
        ->assertSee('A once prop')
        ->assertSee('An optional prop, on demand')
        ->assertSee('Loaded 8 so far')
        ->assertNoJavaScriptErrors();
});

it('polls the layer without moving the page beneath', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/features/panel');

    $page->waitForText('Every request this layer makes targets the layer, not the page beneath.');

    $baseStamp = $page->text('[data-slot="sidebar-inset"] [data-slot="badge"]');
    $layerStamp = $page->text('dialog[data-layer-index] section:nth-of-type(1) [data-slot="badge"]');
    $onceStamp = $page->text('dialog[data-layer-index] section:nth-of-type(3) [data-slot="badge"]');

    $page->click('Start polling')
        ->wait(5)
        ->click('Stop polling')
        ->assertPathIs('/features/layers/features/panel')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertNoJavaScriptErrors();

    expect($page->text('dialog[data-layer-index] section:nth-of-type(1) [data-slot="badge"]'))->not->toBe($layerStamp);
    expect($page->text('dialog[data-layer-index] section:nth-of-type(3) [data-slot="badge"]'))->toBe($onceStamp);
    expect($page->text('[data-slot="sidebar-inset"] [data-slot="badge"]'))->toBe($baseStamp);
});

it('merges props into the layer rather than replacing them', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/features/panel');

    $page->waitForText('Merged props')
        ->assertCount('dialog[data-layer-index] section:nth-of-type(2) ul li', 1);

    $groups = $page->text('dialog[data-layer-index] section:nth-of-type(2) dl');

    $page->click('Ask again')
        ->wait(2)
        ->assertCount('dialog[data-layer-index] section:nth-of-type(2) ul li', 2)
        ->assertNoJavaScriptErrors();

    // deepMerge appends inside each key rather than replacing the nested object.
    expect(substr_count($page->text('dialog[data-layer-index] section:nth-of-type(2) dl'), ':'))
        ->toBeGreaterThan(substr_count($groups, ':'));
});

it('opens a prefetched layer link without a second round trip', function () {
    $this->actingAs(User::factory()->create());
    Contact::factory(10)->create();

    $page = visit('/features/layers/features');

    $requests = 'performance.getEntriesByType("resource").filter((entry) => entry.name.includes("/features/layers/features/panel")).length';

    $page->assertScript($requests, 0)
        ->hover('[data-test="prefetched-open"]')
        ->wait(2)
        ->assertScript($requests, 1)
        ->click('[data-test="prefetched-open"]')
        ->waitForText('Merged props')
        ->assertScript($requests, 1)
        ->assertNoJavaScriptErrors();
});

it('loads an optional prop into the layer on demand', function () {
    $this->actingAs(User::factory()->create());
    Contact::factory(3)->create();

    $page = visit('/features/layers/features/panel');

    $page->waitForText('An optional prop, on demand')
        ->assertSee('absent until asked for')
        ->click('Load it')
        ->waitForText('Generated at')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertNoJavaScriptErrors();
});

it('paginates the layer scroll prop inside the dialog', function () {
    $this->actingAs(User::factory()->create());
    Contact::factory(20)->create();

    $page = visit('/features/layers/features/panel');

    $page->waitForText('Loaded 8 so far');

    $page->script('document.querySelector("dialog[data-layer-index] .max-h-40").scrollTo(0, 9999)');

    $page->waitForText('Loaded 16 so far')
        ->assertPathIs('/features/layers/features/panel')
        ->assertNoJavaScriptErrors();
});

it('fires when-visible inside the layer', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/features/panel');

    $page->waitForText('WhenVisible');

    $page->script('const d = document.querySelector("dialog[data-layer-index] > div.overflow-y-auto"); d.scrollTo(0, d.scrollHeight)');

    $page->waitForText('Loaded when it came into view')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertNoJavaScriptErrors();
});
