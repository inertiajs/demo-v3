<?php

use App\Models\User;
use Inertia\Support\Header;

test('global rate limiter returns 429 when exceeded', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    for ($i = 0; $i < 120; $i++) {
        $this->get(route('dashboard'));
    }

    $this->get(route('dashboard'))->assertTooManyRequests();
});

test('partial reloads have their own higher limit', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $partialReloadHeaders = [
        Header::INERTIA => 'true',
        Header::PARTIAL_COMPONENT => 'Features/DataLoading/Polling',
        Header::PARTIAL_ONLY => 'currentTime',
    ];

    for ($i = 0; $i < 600; $i++) {
        $this->get(route('features.data-loading.polling'), $partialReloadHeaders);
    }

    $this->get(route('dashboard'))->assertOk();
    $this->get(route('features.data-loading.polling'), $partialReloadHeaders)->assertTooManyRequests();
});
