<?php

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;

beforeEach(function () {
    $this->markTestSkipped('The Pest browser server does not support the QUERY method yet.');

    $this->actingAs(User::factory()->create());

    $acme = Organization::factory()->create(['name' => 'Acme']);
    $globex = Organization::factory()->create(['name' => 'Globex']);

    Contact::factory()->for($acme)->create(['first_name' => 'John', 'last_name' => 'Doe', 'is_favorite' => true]);
    Contact::factory()->for($acme)->create(['first_name' => 'Jane', 'last_name' => 'Roe', 'is_favorite' => false]);
    Contact::factory()->for($globex)->create(['first_name' => 'Jack', 'last_name' => 'Smith', 'is_favorite' => false]);
});

it('filters results with a QUERY visit without changing the url', function () {
    $page = visit('/features/navigation/query-visits');

    $page->assertSee('QUERY Visits')
        ->assertSee('v3.10')
        ->assertSeeIn('[data-test="request-method"]', 'GET')
        ->assertSeeIn('[data-test="matches"]', '3')
        ->click('Acme')
        ->click('Search')
        ->assertSeeIn('[data-test="request-method"]', 'QUERY')
        ->assertSeeIn('[data-test="request-body"]', '"organizations"')
        ->assertSeeIn('[data-test="matches"]', '2')
        ->assertSee('John Doe')
        ->assertDontSee('Jack Smith')
        ->assertPathIs('/features/navigation/query-visits')
        ->assertQueryStringMissing('organizations')
        ->assertNoJavaScriptErrors();
});

it('replays the QUERY body when reloading the results', function () {
    $page = visit('/features/navigation/query-visits');

    $page->fill('#query-search', 'Jane')
        ->click('Search')
        ->assertSeeIn('[data-test="request-method"]', 'QUERY')
        ->assertSee('Jane Roe')
        ->assertDontSee('John Doe')
        ->click('Reload Results')
        ->assertSeeIn('[data-test="request-method"]', 'QUERY')
        ->assertSeeIn('[data-test="request-body"]', '"search": "Jane"')
        ->assertSee('Jane Roe')
        ->assertDontSee('John Doe')
        ->assertNoJavaScriptErrors();
});

it('makes QUERY visits with a link and clears them with a GET visit', function () {
    $page = visit('/features/navigation/query-visits');

    $page->click('Favorite contacts')
        ->assertSeeIn('[data-test="request-method"]', 'QUERY')
        ->assertSeeIn('[data-test="matches"]', '1')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Roe')
        ->click('Clear')
        ->assertSeeIn('[data-test="request-method"]', 'GET')
        ->assertSeeIn('[data-test="matches"]', '3')
        ->assertNoJavaScriptErrors();
});
