<?php

use App\Models\User;

it('renders the cold opens page without waiting on anything', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/cold');

    $page->assertSee('Cold Opens')
        ->assertSee('Open one cold')
        ->assertSee('What you are watching')
        ->assertSee('How the placeholder is chosen')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertNoJavaScriptErrors();
});

it('draws a skeleton of the app while the base is on its way', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/cold/panel?delay=1500&loading=shell');

    // One expression, because the walk lands between two round trips and the window closes with it.
    $page->assertScript('document.querySelectorAll("[data-test=layer-loading] [data-slot=skeleton]").length > 0 && document.querySelectorAll("[data-slot=sidebar-inset]").length === 0 && document.querySelectorAll("dialog[data-layer-index]").length === 1')
        ->waitForText('Cold Opens')
        ->assertNotPresent('[data-test="layer-loading"]')
        ->assertPresent('[data-slot="sidebar-inset"]')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertNoJavaScriptErrors();
});

it('draws a plain indicator clear of the dialog', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/cold/panel?delay=1500&loading=plain');

    // The indicator has to be somewhere the dialog is not, or it is a spinner nobody ever sees.
    $page->assertScript('(() => { const s = document.querySelector("[role=status]")?.getBoundingClientRect(); const d = document.querySelector("dialog[data-layer-index]")?.getBoundingClientRect(); return !!s && !!d && (s.bottom <= d.top || s.top >= d.bottom || s.right <= d.left || s.left >= d.right); })()')
        ->assertNotPresent('[data-test="layer-loading"] [data-slot="skeleton"]')
        ->waitForText('Cold Opens')
        ->assertNotPresent('[data-test="layer-loading"]')
        ->assertNoJavaScriptErrors();
});

it('leaves the placeholder standing when the layer is dismissed early', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/cold/panel?delay=4000&loading=shell');

    $page->keys('dialog[data-layer-index]', 'Escape')
        ->assertScript('document.querySelectorAll("[data-test=layer-loading]").length === 1 && document.querySelectorAll("dialog[data-layer-index]").length === 0')
        ->waitForText('Cold Opens')
        ->assertNotPresent('[data-test="layer-loading"]')
        ->assertPathIs('/features/layers/cold')
        ->assertNoJavaScriptErrors();
});

it('draws nothing at all when the resolver answers with nothing', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/cold/panel?delay=1500&loading=none');

    $page->assertScript('document.getElementById("app").children.length === 1 && document.getElementById("app").firstElementChild.tagName === "DIALOG"')
        ->waitForText('Cold Opens')
        ->assertPresent('[data-slot="sidebar-inset"]')
        ->assertNoJavaScriptErrors();
});

it('lands on the base once the walk answers', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/cold/panel?delay=1000&loading=shell');

    $page->waitForText('Cold Opens')
        ->assertPathIs('/features/layers/cold/panel')
        ->keys('dialog[data-layer-index]', 'Escape')
        ->wait(1)
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertPathIs('/features/layers/cold')
        ->assertSee('Cold Opens')
        ->assertNoJavaScriptErrors();
});

it('draws no placeholder when a layer opens over a page already on screen', function () {
    $this->actingAs(User::factory()->create());

    // Data in a Layer opens its panel with a <Link>, so the base never leaves the screen and
    // there is no blank base to draw for.
    $page = visit('/features/layers/features');

    $page->click('Open')
        ->waitForText('Every request this layer makes targets the layer, not the page beneath.')
        ->assertNotPresent('[data-test="layer-loading"]')
        ->assertPresent('[data-slot="sidebar-inset"]')
        ->assertNoJavaScriptErrors();
});
