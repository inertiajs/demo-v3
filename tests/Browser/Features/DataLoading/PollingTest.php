<?php

use App\Models\User;

it('renders the polling page with live data', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/data-loading/polling');

    $page->assertSee('Polling')
        ->assertSee('Polling Controls')
        ->assertSee('Live Data')
        ->assertSee('Start Polling')
        ->assertSee('Server Time')
        ->assertNoJavaScriptErrors();
});

it('exposes the poll mode toggle (overlap/cancel/rest)', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/data-loading/polling');

    $page->assertSee('Mode')
        ->assertSee('v3.2')
        ->assertSee('overlap')
        ->assertSee('cancel')
        ->assertSee('rest')
        ->click('cancel')
        ->assertSee('Aborts the in-flight request')
        ->click('rest')
        ->assertSee('Waits the interval after each response')
        ->assertNoJavaScriptErrors();
});

it('toggles polling and increments the poll count', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/data-loading/polling');

    $page->assertSee('Stopped')
        ->click('Start Polling')
        ->assertSee('Active')
        ->waitForText('Stop Polling', 10)
        ->click('Stop Polling')
        ->assertSee('Stopped')
        ->assertNoJavaScriptErrors();
});

it('throttles, pauses, or continues background polls while the tab is hidden', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/data-loading/polling');

    $page->assertSee('Background Tabs')
        ->assertSee('v3.9')
        ->click('Start Background Polls')
        ->assertSee('Stop Background Polls')
        ->wait(1.5)
        ->assertScript("Number(document.querySelector('[data-test=\"total-pause\"]').textContent) >= 1")
        ->script(<<<'JS'
            Object.defineProperty(document, 'hidden', { configurable: true, get: () => true })
            document.dispatchEvent(new Event('visibilitychange'))
        JS);

    $page->wait(3.5)
        ->click('Stop Background Polls')
        ->assertScript("document.querySelector('[data-test=\"hidden-pause\"]').textContent.trim() === '0'")
        ->assertScript("Number(document.querySelector('[data-test=\"hidden-continue\"]').textContent) >= 3")
        ->assertScript("Number(document.querySelector('[data-test=\"hidden-throttle\"]').textContent) <= 1")
        ->assertNoJavaScriptErrors();
});
