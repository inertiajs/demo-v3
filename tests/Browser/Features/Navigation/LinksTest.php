<?php

use App\Models\User;

it('renders the links & methods page', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/navigation/links');

    $page->assertSee('Links & Methods')
        ->assertSee('Programmatic Visits')
        ->assertSee('Server Response')
        ->assertSee('QUERY Request')
        ->assertSee('router.query()')
        ->assertNoJavaScriptErrors();
});

it('shows server timestamp', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/features/navigation/links');

    $page->assertSee('Server timestamp')
        ->assertNoJavaScriptErrors();
});
