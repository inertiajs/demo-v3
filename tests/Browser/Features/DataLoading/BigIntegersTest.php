<?php

use App\Models\User;

it('receives big integers as exact BigInt values when enabled', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/data-loading/big-integers');

    $page->assertSee('Big Integers')
        ->assertSee('v3.8')
        ->assertSeeIn('[data-test="order-id"]', '900719925474099988')
        ->assertSeeIn('[data-test="order-id-type"]', 'bigint')
        ->assertSeeIn('[data-test="order-id-status"]', 'Exact')
        ->assertSeeIn('[data-test="customer-id-type"]', 'number')
        ->assertSeeIn('[data-test="submit-client-code"]', 'router.post')
        ->click('Submit Order ID')
        ->assertSeeIn('[data-test="received-order-id"]', '900719925474099988')
        ->assertSeeIn('[data-test="received-order-id"]', 'Exact')
        ->assertNoJavaScriptErrors();
});

it('rounds big integers when disabled', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/data-loading/big-integers');

    $page->click('Disabled')
        ->assertQueryStringHas('preserve', '0')
        ->assertSeeIn('[data-test="order-id"]', '900719925474100000')
        ->assertSeeIn('[data-test="order-id-type"]', 'number')
        ->assertSeeIn('[data-test="order-id-status"]', 'Rounded')
        ->click('Submit Order ID')
        ->assertSeeIn('[data-test="received-order-id"]', '900719925474100000')
        ->assertSeeIn('[data-test="received-order-id"]', 'Rounded')
        ->assertNoJavaScriptErrors();
});
