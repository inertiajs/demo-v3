<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * The login every visitor of the demo shares.
     */
    public const EMAIL = 'test@example.com';

    /**
     * Seed the demo user.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => self::EMAIL,
        ]);
    }
}
