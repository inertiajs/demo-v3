<?php

namespace App\Http\Controllers\Feature;

use App\Models\Contact;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Sleep;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class NavigationController
{
    public function links(): Response
    {
        return Inertia::render('Features/Navigation/Links', [
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function linksAction(Request $request): RedirectResponse
    {
        return Inertia::flash('message', strtoupper($request->method()).' request received at '.now()->toTimeString())->back();
    }

    public function preserveState(): Response
    {
        return Inertia::render('Features/Navigation/PreserveState', [
            'serverCounter' => random_int(1, 1000),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function preserveScroll(): Response
    {
        return Inertia::render('Features/Navigation/PreserveScroll', [
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function viewTransitions(): Response
    {
        return Inertia::render('Features/Navigation/ViewTransitions');
    }

    public function historyManagement(Request $request): Response
    {
        return Inertia::render('Features/Navigation/HistoryManagement', [
            'visit' => $request->integer('visit', 0),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function historyAction(Request $request): RedirectResponse
    {
        return to_route('features.navigation.history-management');
    }

    public function asyncRequests(Request $request): Response
    {
        if ($request->header('X-Inertia')) {
            Sleep::for(1)->second();
        }

        return Inertia::render('Features/Navigation/AsyncRequests', [
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function asyncSlow(Request $request): Response
    {
        $delay = $request->integer('delay', 2);
        Sleep::for(min($delay, 5))->seconds();

        return Inertia::render('Features/Navigation/AsyncRequests', [
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function instantVisits(): Response
    {
        return Inertia::render('Features/Navigation/InstantVisits', [
            'sourceTimestamp' => now()->toDateTimeString(),
            'message' => 'This is the source page.',
        ]);
    }

    public function instantVisitTarget(Request $request): Response
    {
        $delay = $request->integer('delay', 2);
        Sleep::for(min($delay, 5))->seconds();

        return Inertia::render('Features/Navigation/InstantVisitTarget', [
            'greeting' => 'Hello from the server!',
            'serverTimestamp' => now()->toDateTimeString(),
            'items' => [
                ['id' => 1, 'name' => 'Server Item A'],
                ['id' => 2, 'name' => 'Server Item B'],
                ['id' => 3, 'name' => 'Server Item C'],
            ],
        ]);
    }

    public function manualVisits(Request $request): Response
    {
        return Inertia::render('Features/Navigation/ManualVisits', [
            'timestamp' => now()->toDateTimeString(),
            'counter' => random_int(1, 1000),
        ]);
    }

    public function queryVisits(Request $request): Response
    {
        abort_unless(config('demo.query_method'), 404);

        $filters = [
            'search' => $request->string('search')->toString(),
            'organizations' => array_map('intval', $request->array('organizations')),
            'favorites' => $request->boolean('favorites'),
        ];

        $contacts = Contact::query()
            ->search($filters['search'])
            ->when($filters['organizations'], fn (Builder $query, array $ids) => $query->whereIn('organization_id', $ids))
            ->when($filters['favorites'], fn (Builder $query) => $query->where('is_favorite', true));

        return Inertia::render('Features/Navigation/QueryVisits', [
            'filters' => $filters,
            'organizations' => Organization::query()->orderBy('name')->get(['id', 'name']),
            'contacts' => fn () => (clone $contacts)
                ->with('organization:id,name')
                ->orderBy('last_name')
                ->limit(8)
                ->get()
                ->map(fn (Contact $contact) => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'organization' => $contact->organization?->name,
                    'isFavorite' => $contact->is_favorite,
                ]),
            'matches' => Inertia::defer(fn () => (clone $contacts)->count()),
            'lastRequest' => fn () => [
                'method' => $request->method(),
                'body' => $request->getContent() ?: null,
                'at' => now()->format('H:i:s.v'),
            ],
        ]);
    }

    public function redirectDemo(): Response
    {
        return Inertia::render('Features/Navigation/Redirects', [
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function redirectStandard(): RedirectResponse
    {
        return Inertia::flash('message', 'Redirected back via redirect()->back()')->back();
    }

    public function redirectToRoute(): RedirectResponse
    {
        Inertia::flash('message', 'Redirected via to_route()');

        return to_route('features.navigation.redirects');
    }

    public function redirectExternal(): \Symfony\Component\HttpFoundation\Response
    {
        return Inertia::location('https://cloud.laravel.com');
    }

    public function scrollManagement(): Response
    {
        return Inertia::render('Features/Navigation/ScrollManagement', [
            'timestamp' => now()->toDateTimeString(),
            'items' => collect(range(1, 50))->map(fn (int $i) => [
                'id' => $i,
                'title' => "Item #{$i}",
                'description' => fake()->sentence(),
            ])->all(),
        ]);
    }

    public function urlFragments(): Response
    {
        return Inertia::render('Features/Navigation/UrlFragments', [
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function redirectWithHash(): RedirectResponse
    {
        return redirect('/features/navigation/url-fragments#server-section');
    }

    public function preserveFragmentRedirect(): RedirectResponse
    {
        return redirect('/features/navigation/url-fragments/preserve-target')->preserveFragment();
    }

    public function preserveFragmentTarget(): Response
    {
        return Inertia::render('Features/Navigation/UrlFragments', [
            'timestamp' => now()->toDateTimeString(),
            'redirectedFrom' => 'preserveFragment redirect',
        ]);
    }
}
