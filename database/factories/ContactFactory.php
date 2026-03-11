<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'organization_id' => Organization::factory(),
            'is_favorite' => fake()->boolean(20),
        ];
    }

    public function favorite(): static
    {
        return $this->state(['is_favorite' => true]);
    }

    public function withoutOrganization(): static
    {
        return $this->state(['organization_id' => null]);
    }
}
