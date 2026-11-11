<?php

use App\Models\User;

it('renders the inside a layer page', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/inside');

    $page->assertSee('Inside a Layer')
        ->assertSee('Open the panel')
        ->assertSee('What the layer can do to itself')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertNoJavaScriptErrors();
});

it('refreshes the layer props without touching the page beneath', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/inside/panel');

    $page->waitForText('Everything in here goes through useLayer().')
        ->click('[data-test="layer-get"]')
        ->waitForText('Inside a Layer')
        ->assertQueryStringHas('reloads', '1')
        ->assertCount('dialog[data-layer-index]', 1)
        ->assertNoJavaScriptErrors();
});

it('changes a layer prop without a request', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/inside/panel');

    $page->waitForText('Everything in here goes through useLayer().')
        ->click('[data-test="layer-replace-prop"]')
        ->waitForText('10')
        ->assertPathIs('/features/layers/inside/panel')
        ->assertNoJavaScriptErrors();
});

it('delivers a layer event to the page that opened it', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/inside');

    $page->click('Open the panel')
        ->waitForText('Everything in here goes through useLayer().')
        ->click('[data-test="layer-emit"]')
        ->waitForText('{"from":"panel"}')
        ->assertNoJavaScriptErrors();
});

it('opens a child the panel owns, whose events stop at the panel', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/inside');

    $page->click('Open the panel')
        ->waitForText('Everything in here goes through useLayer().')
        ->click('[data-test="layer-child"]')
        ->waitForText('Opened with layer.layer(), owned by the panel beneath.')
        ->assertCount('dialog[data-layer-index]', 2)
        ->click('Emit to my owner')
        ->waitForText('{"from":"child"}')
        ->assertSeeIn('dialog[data-layer-index="0"]', 'Heard from the child')
        ->assertDontSeeIn('[data-slot="sidebar-inset"] [data-slot="card"]', '{"from":"child"}')
        ->assertNoJavaScriptErrors();
});

it('gives the layer its own title and its own layout slot', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/inside');

    $page->assertTitleContains('Inside a Layer')
        ->click('Open the panel')
        ->waitForText('Everything in here goes through useLayer().')
        ->assertTitleContains('Panel ')
        ->click('[data-test="layer-layout-prop"]')
        ->waitForText('Set by layer')
        ->assertPresent('dialog[data-layer-index] [data-test="layer-notice"]')
        ->keys('dialog[data-layer-index]', 'Escape')
        ->wait(1)
        ->assertTitleContains('Inside a Layer')
        ->assertNotPresent('[data-test="layer-notice"]')
        ->assertNoJavaScriptErrors();
});

it('keeps a remembered draft and a scroll position per tier', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/inside');

    $page->type('[data-slot="sidebar-inset"] input[placeholder="Type something on the page…"]', 'page draft')
        ->click('Open the panel')
        ->waitForText('Its own remembered draft')
        ->assertValue('dialog[data-layer-index] input[placeholder="Type something in the layer…"]', '')
        ->type('dialog[data-layer-index] input[placeholder="Type something in the layer…"]', 'layer draft');

    $page->script('document.querySelector("dialog[data-layer-index] [scroll-region]").scrollTo(0, 240)');

    $page->wait(1)
        ->keys('dialog[data-layer-index]', 'Escape')
        ->wait(1)
        ->assertValue('[data-slot="sidebar-inset"] input[placeholder="Type something on the page…"]', 'page draft')
        ->forward()
        ->waitForText('Its own remembered draft')
        ->wait(1)
        ->assertValue('dialog[data-layer-index] input[placeholder="Type something in the layer…"]', 'layer draft')
        ->assertScript('document.querySelector("dialog[data-layer-index] [scroll-region]").scrollTop', 240)
        ->assertNoJavaScriptErrors();
});

it('gives a re-opened layer a bag of its own rather than the closed one', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/inside');

    $page->click('Open the panel')
        ->waitForText('Its own remembered draft')
        ->type('dialog[data-layer-index] input[placeholder="Type something in the layer…"]', 'layer draft')
        ->keys('dialog[data-layer-index]', 'Escape')
        ->wait(1)
        ->click('Open the panel')
        ->waitForText('Its own remembered draft')
        ->assertValue('dialog[data-layer-index] input[placeholder="Type something in the layer…"]', '')
        ->assertNoJavaScriptErrors();
});

it('closes the layer from the server and flashes back to the page', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/inside');

    $page->click('Open the panel')
        ->waitForText('Everything in here goes through useLayer().')
        ->click('Close from the server')
        ->waitForText('The server closed the layer with Inertia::close().')
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertPathIs('/features/layers/inside')
        ->waitForText('the panel closed')
        ->assertNoJavaScriptErrors();
});

it('closes the layer from the inside', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/layers/inside');

    $page->click('Open the panel')
        ->waitForText('Everything in here goes through useLayer().')
        ->click('[data-test="layer-close"]')
        ->wait(1)
        ->assertNotPresent('dialog[data-layer-index]')
        ->assertPathIs('/features/layers/inside')
        ->assertNoJavaScriptErrors();
});
