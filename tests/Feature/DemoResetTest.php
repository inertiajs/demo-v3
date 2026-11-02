<?php

use App\Events\DemoDataReset;
use App\Models\Contact;
use App\Models\LiveVote;
use App\Models\Note;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([DemoDataReset::class]);

    $this->seed();
});

it('seeds the demo user as the only user', function () {
    expect(User::pluck('email')->all())->toBe([UserSeeder::EMAIL])
        ->and(Note::where('user_id', '!=', User::first()->id)->count())->toBe(0);
});

it('replaces the demo data but keeps the demo user', function () {
    $user = User::first();
    $contact = Contact::first();

    User::factory()->create();
    LiveVote::create(['visitor' => 'Visitor A', 'framework' => 'vue']);

    $this->travel(1)->day();

    $this->artisan('demo:reset')->assertSuccessful();

    expect(User::pluck('id')->all())->toBe([$user->id])
        ->and(Contact::find($contact->id))->toBeNull()
        ->and(Contact::count())->toBe(100)
        ->and(Organization::count())->toBe(15)
        ->and(Note::where('user_id', '!=', $user->id)->count())->toBe(0)
        ->and(LiveVote::count())->toBe(0);

    expect(DemoDataReset::resetAt())->toBe(now()->toDateTimeString());

    Event::assertDispatched(DemoDataReset::class, fn (DemoDataReset $event) => $event->broadcastWith()['__inertia']['props']['demoResetAt'] === now()->toDateTimeString());
});

it('shares the reset time as a live prop', function () {
    $this->actingAs(User::first())
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('demoResetAt', DemoDataReset::resetAt()));
});
