<?php

namespace App\Http\Controllers\Feature;

use App\Http\Requests\Crm\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Http\Resources\NoteResource;
use App\Http\Resources\OrganizationResource;
use App\Models\Contact;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Sleep;
use Illuminate\Validation\ValidationException;
use Inertia\CloseResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LayerController
{
    /**
     * The wizard steps, keyed by their url segment.
     *
     * @var array<string, array{title: string, blurb: string}>
     */
    private const WIZARD = [
        'details' => ['title' => 'Details', 'blurb' => 'Who are we inviting?'],
        'access' => ['title' => 'Access', 'blurb' => 'What may they see?'],
        'review' => ['title' => 'Review', 'blurb' => 'Everything look right?'],
    ];

    public function dialogs(): Response
    {
        return Inertia::render('Features/Layers/Dialogs', [
            'contacts' => ContactResource::collection(
                Contact::query()->with('organization')->orderBy('id')->take(6)->get()
            ),
        ]);
    }

    public function contactCard(Contact $contact): Response
    {
        $contact->load('organization');

        return Inertia::render('Features/Layers/ContactCard', [
            'dialog' => [
                'title' => $contact->name,
                'description' => 'A routed layer rendered as a modal.',
            ],
            'contact' => new ContactResource($contact),
            'notes' => Inertia::defer(fn () => NoteResource::collection(
                $contact->notes()->with('user')->latest()->take(3)->get()
            ))->once(),
        ])->layer(base: route('features.layers.dialogs', absolute: false));
    }

    public function contactEdit(Contact $contact): Response
    {
        $contact->load('organization');

        return Inertia::render('Features/Layers/ContactEdit', [
            'dialog' => [
                'title' => 'Edit contact',
                'description' => 'A routed layer rendered as a slideover.',
                'variant' => 'slideover',
            ],
            'contact' => new ContactResource($contact),
            'organizations' => OrganizationResource::collection(
                Organization::query()->orderBy('name')->get()
            ),
        ])->layer(base: route('features.layers.dialogs', absolute: false));
    }

    public function updateContact(UpdateContactRequest $request, Contact $contact): CloseResponse
    {
        $contact->update($request->validated());

        Inertia::flash('message', 'Contact updated from a layer.');

        return Inertia::close();
    }

    public function toggleContactFavorite(Request $request, Contact $contact): RedirectResponse
    {
        // Slow enough that the optimistic props are on screen before the server answers.
        Sleep::for(700)->milliseconds();

        if ($request->boolean('refuse')) {
            throw ValidationException::withMessages([
                'refuse' => 'The server refused, so the optimistic change rolled back.',
            ]);
        }

        $contact->update(['is_favorite' => ! $contact->is_favorite]);

        return Inertia::flash('message', 'Favorite updated from a layer.')->back();
    }

    public function stacked(): Response
    {
        return Inertia::render('Features/Layers/Stacked', [
            'organizations' => OrganizationResource::collection(
                Organization::query()->withCount('contacts')->orderBy('name')->take(5)->get()
            ),
        ]);
    }

    public function organization(Organization $organization): Response
    {
        return Inertia::render('Features/Layers/Organization', [
            'dialog' => [
                'title' => $organization->name,
                'description' => 'Level 1 — a slideover.',
                'variant' => 'slideover',
                'size' => 'lg',
            ],
            'organization' => new OrganizationResource($organization),
            'contacts' => ContactResource::collection(
                $organization->contacts()->orderBy('first_name')->take(8)->get()
            ),
        ])->layer(base: route('features.layers.stacked', absolute: false));
    }

    public function organizationContact(Organization $organization, Contact $contact): Response
    {
        return Inertia::render('Features/Layers/StackedContact', [
            'dialog' => [
                'title' => $contact->name,
                'description' => 'Level 2 — a modal on top of the slideover.',
            ],
            'organization' => new OrganizationResource($organization),
            'contact' => new ContactResource($contact),
            'notes' => NoteResource::collection($contact->notes()->with('user')->latest()->take(5)->get()),
        ])->layer(base: route('features.layers.organization', $organization, absolute: false));
    }

    public function storeNote(Request $request, Organization $organization, Contact $contact): RedirectResponse
    {
        $request->validate(['body' => ['required', 'string', 'min:3', 'max:255']]);

        $contact->notes()->create([
            'body' => $request->string('body'),
            'user_id' => $request->user()->id,
        ]);

        Inertia::flash('message', 'Note added from a stacked layer.');

        return redirect()->route('features.layers.organization.contact', [$organization, $contact]);
    }

    public function local(): Response
    {
        return Inertia::render('Features/Layers/Local', [
            'contacts' => ContactResource::collection(
                Contact::query()->orderBy('id')->take(5)->get()
            ),
        ]);
    }

    public function toggleFavorite(Contact $contact): RedirectResponse
    {
        $contact->update(['is_favorite' => ! $contact->is_favorite]);

        return Inertia::flash('message', $contact->is_favorite ? 'Added to favorites.' : 'Removed from favorites.')->back();
    }

    public function inside(): Response
    {
        return Inertia::render('Features/Layers/Inside');
    }

    public function insidePanel(Request $request): Response
    {
        return Inertia::render('Features/Layers/Panel', [
            'dialog' => [
                'title' => 'A layer that drives itself',
                'description' => 'Everything in here goes through useLayer().',
            ],
            'reloads' => (int) $request->query('reloads', '0'),
            'token' => str()->upper(str()->random(6)),
        ])->layer(base: route('features.layers.inside', absolute: false));
    }

    public function insideChild(): Response
    {
        return Inertia::render('Features/Layers/Child', [
            'dialog' => [
                'title' => 'A child layer',
                'description' => 'Opened with layer.layer(), owned by the panel beneath.',
                'variant' => 'slideover',
                'size' => 'sm',
            ],
        ])->layer(base: route('features.layers.inside', absolute: false));
    }

    public function insideClose(): CloseResponse
    {
        Inertia::flash('message', 'The server closed the layer with Inertia::close().');

        return Inertia::close();
    }

    public function features(): Response
    {
        return Inertia::render('Features/Layers/Features', [
            'baseLoadedAt' => now()->format('H:i:s'),
        ]);
    }

    public function featuresPanel(): Response
    {
        return Inertia::render('Features/Layers/FeaturesPanel', [
            'dialog' => [
                'title' => 'The rest of Inertia, in here',
                'description' => 'Every request this layer makes targets the layer, not the page beneath.',
                'size' => 'lg',
            ],
            'tick' => now()->format('H:i:s'),
            'openedAt' => Inertia::once(fn () => now()->format('H:i:s')),
            'feed' => Inertia::merge([
                ['id' => random_int(1000, 9999), 'at' => now()->format('H:i:s')],
            ]),
            'groups' => Inertia::deepMerge([
                'recent' => [now()->format('H:i:s')],
                'starred' => [now()->format('H:i:s')],
            ]),
            'contacts' => Inertia::scroll(
                ContactResource::collection(Contact::query()->orderBy('id')->cursorPaginate(8))
            ),
            'onDemand' => Inertia::optional(fn () => [
                'generatedAt' => now()->format('H:i:s'),
                'contacts' => Contact::query()->limit(3)->get()
                    ->map(fn (Contact $contact) => ['id' => $contact->id, 'name' => $contact->name])
                    ->all(),
            ]),
            'farBelow' => Inertia::optional(function () {
                Sleep::for(400)->milliseconds();

                return ['generatedAt' => now()->format('H:i:s')];
            }),
        ])->layer(base: route('features.layers.features', absolute: false));
    }

    public function cold(Request $request): Response
    {
        // Only the walk asks for a delay, so landing on this page from the sidebar is instant.
        Sleep::for($request->integer('delay'))->milliseconds();

        return Inertia::render('Features/Layers/Cold', [
            'baseLoadedAt' => now()->format('H:i:s'),
        ]);
    }

    public function coldPanel(Request $request): Response
    {
        $delay = max(0, min(10000, $request->integer('delay', 3000)));
        $loading = $request->string('loading', 'shell')->toString();

        return Inertia::render('Features/Layers/ColdPanel', [
            'dialog' => [
                'title' => 'A layer that arrived first',
                'description' => 'The page underneath is still on its way.',
            ],
            'delay' => $delay,
            'loading' => $loading,
            // The treatment rides on the base url, because that is the url the client is walking
            // to and all the loading resolver is given to choose by.
        ])->layer(base: route('features.layers.cold', ['delay' => $delay, 'loading' => $loading], absolute: false));
    }

    public function wizard(): Response
    {
        return Inertia::render('Features/Layers/Wizard');
    }

    public function wizardStep(string $step): Response
    {
        $steps = array_keys(self::WIZARD);
        $index = array_search($step, $steps, true);

        return Inertia::render('Features/Layers/WizardStep', [
            'dialog' => [
                'title' => 'Invite a teammate',
                'description' => sprintf('Step %d of %d — %s', $index + 1, count($steps), self::WIZARD[$step]['title']),
            ],
            'step' => $step,
            'steps' => $steps,
            'blurb' => self::WIZARD[$step]['blurb'],
        ])->layer(base: route('features.layers.wizard', absolute: false), key: 'wizard');
    }

    public function wizardHelp(): Response
    {
        return Inertia::render('Features/Layers/WizardHelp', [
            'dialog' => [
                'title' => 'What is a key?',
                'description' => 'A layer of its own, stacked on the wizard.',
                'variant' => 'slideover',
            ],
        ])->layer(base: route('features.layers.wizard', absolute: false), key: 'wizard-help');
    }

    public function detour(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->get('layers.confirmed')) {
            return redirect()->route('features.layers.detour.confirm')->interstitial();
        }

        return Inertia::render('Features/Layers/Secret', [
            'dialog' => [
                'title' => 'The guarded layer',
                'description' => 'You came back here through the detour.',
            ],
        ])->layer(base: route('features.layers.detours', absolute: false));
    }

    public function detours(Request $request): Response
    {
        return Inertia::render('Features/Layers/Detours', [
            'confirmed' => (bool) $request->session()->get('layers.confirmed'),
            'renderedAt' => now()->format('H:i:s'),
        ]);
    }

    public function detourConfirm(): Response
    {
        return Inertia::render('Features/Layers/DetourConfirm');
    }

    public function detourComplete(Request $request): RedirectResponse
    {
        $request->session()->put('layers.confirmed', true);

        return redirect()->route('features.layers.detour');
    }

    public function detourForget(Request $request): RedirectResponse
    {
        $request->session()->forget('layers.confirmed');

        return Inertia::flash('message', 'The gate is locked again.')->back();
    }

    public function detourDenied(): never
    {
        abort(403, 'This layer is not yours to open.');
    }

    public function detourMissing(): never
    {
        abort(404, 'There is no layer here.');
    }

    public function detourBroken(): never
    {
        abort(500, 'The layer blew up on the way out.');
    }
}
