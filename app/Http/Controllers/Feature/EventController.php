<?php

namespace App\Http\Controllers\Feature;

use Illuminate\Support\Sleep;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EventController
{
    public function globalEvents(): Response
    {
        return Inertia::render('Features/Events/GlobalEvents');
    }

    public function globalEventsAction(): RedirectResponse
    {
        return Inertia::flash('message', 'Action completed successfully!')->back();
    }

    public function onceEvents(): Response
    {
        return Inertia::render('Features/Events/OnceEvents');
    }

    public function onceEventsAction(): RedirectResponse
    {
        return Inertia::flash('message', 'One-shot action fired!')->back();
    }

    public function visitCallbacks(): Response
    {
        return Inertia::render('Features/Events/VisitCallbacks');
    }

    public function visitCallbacksAction(): RedirectResponse
    {
        return Inertia::flash('message', 'Visit callback action completed!')->back();
    }

    public function progress(): Response
    {
        return Inertia::render('Features/Events/Progress');
    }

    public function progressSlow(): Response
    {
        Sleep::for(2)->seconds();

        return Inertia::render('Features/Events/Progress');
    }

    public function locationEvent(): Response
    {
        return Inertia::render('Features/Events/LocationEvent');
    }

    public function deployNewVersion(): RedirectResponse
    {
        Inertia::version('new-asset-version-hash');

        return back();
    }
}
