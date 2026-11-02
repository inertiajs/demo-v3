<?php

use App\Models\User;

it('renders the links page for QUERY visits', function () {
    $this->actingAs(User::factory()->create());

    $this->withHeaders(['X-Inertia' => 'true'])
        ->json('QUERY', route('features.navigation.links'), ['search' => 'Inertia'])
        ->assertOk()
        ->assertJsonPath('component', 'Features/Navigation/Links');
});
