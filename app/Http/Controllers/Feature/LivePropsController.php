<?php

namespace App\Http\Controllers\Feature;

use App\Events\CounterTicked;
use App\Events\ReactionAdded;
use App\Events\VoteCast;
use App\Models\LiveReaction;
use App\Models\LiveTick;
use App\Models\LiveVote;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LivePropsController
{
    protected const FRAMEWORKS = ['vue', 'react', 'svelte'];

    protected const REACTIONS = ['thumbsUp', 'party', 'heart', 'rocket'];

    public function show(Request $request): Response
    {
        $visitor = $this->visitor($request);

        // Every visitor shares one public channel, so a vote in one browser
        // refreshes the poll for everyone else on the page
        return Inertia::render('Features/DataLoading/LiveProps', [
            'visitor' => $visitor,
            'votes' => Inertia::live(fn () => $this->votes(), on: VoteCast::class),
            // Resolved for each visitor, which is why it refreshes rather than travelling with the event
            'yourVote' => Inertia::live(fn () => LiveVote::where('visitor', $visitor)->value('framework'), on: VoteCast::class),
            'reactions' => Inertia::live(fn () => $this->reactions(), on: ReactionAdded::class),
            'ticks' => Inertia::live(fn () => LiveTick::count(), on: CounterTicked::class),
            'slowTicks' => Inertia::live(fn () => LiveTick::count(), on: CounterTicked::class, throttle: 3000),
        ]);
    }

    public function castVote(Request $request): HttpResponse
    {
        $validated = $request->validate([
            'framework' => ['required', Rule::in(self::FRAMEWORKS)],
            'toOthers' => ['boolean'],
        ]);

        $visitor = $this->visitor($request);

        // One vote per visitor, so voting again moves the vote
        $vote = LiveVote::updateOrCreate(['visitor' => $visitor], ['framework' => $validated['framework']]);

        if (! $vote->wasRecentlyCreated && ! $vote->wasChanged('framework')) {
            return response()->noContent();
        }

        $broadcast = broadcast(new VoteCast($visitor, $validated['framework']));

        if ($request->boolean('toOthers')) {
            $broadcast->toOthers();
        }

        return response()->noContent();
    }

    public function addReaction(Request $request): HttpResponse
    {
        $validated = $request->validate([
            'reaction' => ['required', Rule::in(self::REACTIONS)],
        ]);

        $visitor = $this->visitor($request);

        LiveReaction::create(['visitor' => $visitor, 'reaction' => $validated['reaction']]);

        broadcast(new ReactionAdded($visitor, $validated['reaction'], $this->reactions()));

        return response()->noContent();
    }

    public function burstTicks(): HttpResponse
    {
        // Broadcast after the response, so the page sees the events arrive over time
        dispatch(function () {
            foreach (range(1, 20) as $tick) {
                LiveTick::create();

                broadcast(new CounterTicked);

                Sleep::for(200)->milliseconds();
            }
        })->afterResponse();

        return response()->noContent();
    }

    protected function visitor(Request $request): string
    {
        // Everyone shares the demo login, so each browser session is its own visitor
        return $request->session()->remember('live-props.visitor', fn () => 'Visitor '.Str::upper(Str::random(6)));
    }

    /**
     * @return array<string, int>
     */
    protected function votes(): array
    {
        $votes = LiveVote::pluck('framework')->countBy();

        return collect(self::FRAMEWORKS)->mapWithKeys(fn (string $framework) => [$framework => $votes->get($framework, 0)])->all();
    }

    /**
     * @return array<string, int>
     */
    protected function reactions(): array
    {
        return collect(self::REACTIONS)->mapWithKeys(fn (string $reaction) => [$reaction => LiveReaction::where('reaction', $reaction)->count()])->all();
    }
}
