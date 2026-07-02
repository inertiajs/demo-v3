<?php

use App\Models\User;

it('responds with a 409 location visit carrying the new version on a version mismatch', function () {
    $response = $this->actingAs(User::factory()->create())
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => 'stale-version',
        ])
        ->get('/features/events/location-event/deploy');

    $response->assertStatus(409)
        ->assertHeader('X-Inertia-Location', url('/features/events/location-event/deploy'))
        ->assertHeader('X-Inertia-Version', 'new-asset-version-hash');
});
