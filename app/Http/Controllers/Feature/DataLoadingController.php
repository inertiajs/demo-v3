<?php

namespace App\Http\Controllers\Feature;

use App\Exceptions\FlakyReportException;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Sleep;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DataLoadingController
{
    public function deferredProps(Request $request): Response
    {
        return Inertia::render('Features/DataLoading/DeferredProps', [
            'quickStat' => 'Loaded instantly',
            'slowStats' => Inertia::defer(function () {
                Sleep::for(800)->milliseconds();

                return [
                    'totalContacts' => Contact::count(),
                    'totalFavorites' => Contact::where('is_favorite', true)->count(),
                ];
            }),
            'heavyData' => Inertia::defer(function () {
                Sleep::for(1500)->milliseconds();

                return Contact::query()->latest()->limit(5)->get()->map(fn (Contact $contact) => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                ])->all();
            }, 'heavy'),
            'flakyReport' => Inertia::defer(function () use ($request) {
                Sleep::for(600)->milliseconds();

                if (! $request->hasHeader('X-Force-Success')) {
                    throw new FlakyReportException('Upstream report service is unavailable.');
                }

                return ['value' => random_int(1000, 9999)];
            }, rescue: true),
        ]);
    }

    public function partialReloads(): Response
    {
        return Inertia::render('Features/DataLoading/PartialReloads', [
            'users' => fn () => [
                ['id' => 1, 'name' => 'Alice', 'role' => 'Admin'],
                ['id' => 2, 'name' => 'Bob', 'role' => 'Editor'],
                ['id' => 3, 'name' => 'Charlie', 'role' => 'Viewer'],
            ],
            'stats' => fn () => [
                'total' => Contact::count(),
                'favorites' => Contact::where('is_favorite', true)->count(),
            ],
            'timestamp' => fn () => now()->toDateTimeString(),
            'randomNumber' => fn () => random_int(1, 1000),
        ]);
    }

    public function infiniteScroll(Request $request): Response
    {
        $query = Contact::query()
            ->when($request->boolean('favorites'), fn (Builder $query) => $query->where('is_favorite', true))
            ->latest()
            ->orderByDesc('id');

        return Inertia::render('Features/DataLoading/InfiniteScroll', [
            'contacts' => Inertia::scroll(
                ContactResource::collection($query->cursorPaginate(10))
            ),
        ]);
    }

    public function whenVisible(): Response
    {
        return Inertia::render('Features/DataLoading/WhenVisible', [
            'section1' => Inertia::optional(function () {
                Sleep::for(500)->milliseconds();

                return Contact::query()->limit(3)->get()->map(fn (Contact $contact) => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                ])->all();
            }),
            'section2' => Inertia::optional(function () {
                Sleep::for(800)->milliseconds();

                return [
                    'totalContacts' => Contact::count(),
                    'generated' => now()->toDateTimeString(),
                ];
            }),
            'section3' => Inertia::optional(function () {
                Sleep::for(600)->milliseconds();

                return Contact::query()
                    ->where('is_favorite', true)
                    ->limit(5)
                    ->get()
                    ->map(fn (Contact $contact) => [
                        'id' => $contact->id,
                        'name' => $contact->name,
                    ])->all();
            }),
        ]);
    }

    public function polling(): Response
    {
        return Inertia::render('Features/DataLoading/Polling', [
            'currentTime' => now()->toDateTimeString(),
            // Partial reloads that only ask for the current time skip the delay
            'randomNumber' => function () {
                Sleep::for(random_int(500, 3000))->milliseconds();

                return random_int(1, 1000);
            },
            'contactCount' => Contact::count(),
        ]);
    }

    public function propMerging(): Response
    {
        $pool = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
            ['id' => 3, 'name' => 'Charlie'],
            ['id' => 4, 'name' => 'Diana'],
            ['id' => 5, 'name' => 'Eve'],
        ];

        $resetProps = explode(',', request()->header('X-Inertia-Reset', ''));
        $isPartialReload = request()->header('X-Inertia-Partial-Data') !== null;

        if (in_array('contacts', $resetProps)) {
            $count = 1;
        } elseif ($isPartialReload) {
            $count = min(session('prop_merging_count', 1) + 1, 5);
        } else {
            $count = 1;
        }

        session(['prop_merging_count' => $count]);

        return Inertia::render('Features/DataLoading/PropMerging', [
            'notifications' => Inertia::merge([
                [
                    'id' => random_int(1000, 9999),
                    'message' => 'Notification at '.now()->toTimeString(),
                    'type' => Arr::random(['info', 'success', 'warning', 'error', 'alert', 'update', 'reminder', 'system', 'promo', 'social']),
                ],
            ]),
            'activities' => Inertia::merge([
                [
                    'id' => random_int(1000, 9999),
                    'action' => Arr::random(['created', 'updated', 'deleted', 'restored', 'archived', 'published']),
                    'subject' => Arr::random(['Contact', 'Organization', 'Note', 'Invoice', 'Report', 'Task']),
                    'time' => now()->toTimeString(),
                ],
            ])->prepend(),
            'contacts' => Inertia::merge(
                collect($pool)->take($count)->map(fn (array $contact) => [
                    ...$contact,
                    'updated' => now()->toTimeString(),
                ])->all(),
            )->matchOn('id'),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function optionalProps(): Response
    {
        return Inertia::render('Features/DataLoading/OptionalProps', [
            'regularData' => [
                'timestamp' => now()->toDateTimeString(),
                'message' => 'This prop is always included in the response.',
            ],
            'optionalData' => Inertia::optional(function () {
                Sleep::for(500)->milliseconds();

                return [
                    'generatedAt' => now()->toDateTimeString(),
                    'contacts' => Contact::query()->limit(3)->get()->map(fn (Contact $contact) => [
                        'id' => $contact->id,
                        'name' => $contact->name,
                    ])->all(),
                ];
            }),
            'deferredData' => Inertia::defer(function () {
                Sleep::for(600)->milliseconds();

                return [
                    'generatedAt' => now()->toDateTimeString(),
                    'totalContacts' => Contact::count(),
                ];
            }),
        ]);
    }

    public function onceProps(int $page = 1): Response
    {
        return Inertia::render('Features/DataLoading/OnceProps', [
            'page' => $page,
            'staticData' => Inertia::once(fn () => [
                'generatedAt' => now()->toDateTimeString(),
                'randomId' => random_int(1000, 9999),
            ]),
            'freshData' => Inertia::once(fn () => [
                'generatedAt' => now()->toDateTimeString(),
                'value' => random_int(1, 1000),
            ])->fresh(),
            'expiringData' => Inertia::once(fn () => [
                'generatedAt' => now()->toDateTimeString(),
                'value' => random_int(1, 1000),
            ])->until(now()->addSeconds(5)),
            'aliasedData' => Inertia::once(fn () => [
                'generatedAt' => now()->toDateTimeString(),
                'value' => random_int(1, 1000),
            ])->as('shared-once-key'),
            'dynamicData' => [
                'timestamp' => now()->toDateTimeString(),
                'randomNumber' => random_int(1, 1000),
            ],
        ]);
    }

    public function bigIntegers(Request $request): Response
    {
        $preserve = $request->boolean('preserve', true);

        return Inertia::render('Features/DataLoading/BigIntegers', [
            'preserve' => $preserve,
            'orderId' => 900719925474099988,
            'expectedOrderId' => '900719925474099988',
            'customerId' => 42,
        ])->preserveBigIntegers($preserve);
    }

    public function submitBigInteger(Request $request): RedirectResponse
    {
        $request->validate(['id' => ['required', 'integer']]);

        return Inertia::flash('receivedOrderId', (string) $request->integer('id'))->back();
    }
}
