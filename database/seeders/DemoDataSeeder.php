<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Note;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', UserSeeder::EMAIL)->firstOrFail();

        $organizations = Organization::factory(15)->create();

        // Create contacts — some with organizations, some without
        $contacts = Contact::factory(75)
            ->recycle($organizations)
            ->create();

        $contactsWithoutOrg = Contact::factory(25)
            ->withoutOrganization()
            ->create();

        $allContacts = $contacts->merge($contactsWithoutOrg);

        // Create notes spread across contacts, all written by the demo user
        $allContacts->random(40)->each(function (Contact $contact) use ($user) {
            Note::factory(rand(1, 5))
                ->for($user)
                ->for($contact)
                ->create();
        });
    }
}
