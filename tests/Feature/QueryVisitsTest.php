<?php

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());

    $acme = Organization::factory()->create(['name' => 'Acme']);
    $globex = Organization::factory()->create(['name' => 'Globex']);

    Contact::factory()->for($acme)->create(['first_name' => 'John', 'last_name' => 'Doe', 'is_favorite' => true]);
    Contact::factory()->for($acme)->create(['first_name' => 'Jane', 'last_name' => 'Roe', 'is_favorite' => false]);
    Contact::factory()->for($globex)->create(['first_name' => 'Jack', 'last_name' => 'Smith', 'is_favorite' => false]);

    $this->acme = $acme;
});

it('filters contacts with the QUERY request body', function () {
    $response = $this->withHeaders(['X-Inertia' => 'true'])
        ->json('QUERY', route('features.navigation.query-visits'), [
            'organizations' => [$this->acme->id],
            'favorites' => true,
        ]);

    $response->assertOk()
        ->assertJsonPath('component', 'Features/Navigation/QueryVisits')
        ->assertJsonPath('props.filters.organizations', [$this->acme->id])
        ->assertJsonPath('props.filters.favorites', true)
        ->assertJsonPath('props.lastRequest.method', 'QUERY')
        ->assertJsonCount(1, 'props.contacts')
        ->assertJsonPath('props.contacts.0.name', 'John Doe');
});

it('applies the QUERY request body to partial reloads', function () {
    $response = $this->withHeaders([
        'X-Inertia' => 'true',
        'X-Inertia-Partial-Component' => 'Features/Navigation/QueryVisits',
        'X-Inertia-Partial-Data' => 'contacts,matches',
    ])->json('QUERY', route('features.navigation.query-visits'), [
        'search' => 'Jane',
    ]);

    $response->assertOk()
        ->assertJsonCount(1, 'props.contacts')
        ->assertJsonPath('props.contacts.0.name', 'Jane Roe')
        ->assertJsonPath('props.matches', 1)
        ->assertJsonMissingPath('props.organizations');
});

it('renders every contact for a plain GET visit', function () {
    $response = $this->get(route('features.navigation.query-visits'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('lastRequest.method', 'GET')
        ->where('lastRequest.body', null)
        ->has('contacts', 3)
    );
});

it('hides the QUERY demos when the QUERY method is disabled', function () {
    config(['demo.query_method' => false]);

    $this->get(route('features.navigation.links'))
        ->assertInertia(fn ($page) => $page->where('queryMethod', false));

    $this->get(route('features.navigation.query-visits'))->assertNotFound();
});
