<?php

use App\Models\User;

beforeEach(function () {
    config(['broadcasting.default' => 'reverb']);

    require base_path('routes/channels.php');
});

it('authorizes users for their own private channel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => "private-App.Models.User.{$user->id}",
        ])
        ->assertOk()
        ->assertJsonStructure(['auth']);
});

it('denies users access to another user\'s private channel', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->post('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => "private-App.Models.User.{$otherUser->id}",
        ])
        ->assertForbidden();
});
