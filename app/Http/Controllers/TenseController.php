<?php

namespace App\Http\Controllers;

use App\Enums\TenseTime;
use App\Http\Requests\Library\TenseIndexRequest;
use App\Models\Tense;
use Inertia\Inertia;
use Inertia\Response;

class TenseController extends Controller
{
    /**
     * Browse the global Tense knowledge base as a list (name, time, aspect,
     * summary) — not the full structure/examples — filterable by time,
     * ordered by the canonical reference order. Only ~12-15 rows total, so
     * unlike Word Bank/Mistakes this is returned unpaginated.
     */
    public function index(TenseIndexRequest $request): Response
    {
        $filters = $request->validated();

        $time = filled($filters['time'] ?? null) ? TenseTime::from((string) $filters['time']) : null;

        $tenses = Tense::query()
            ->when($time, fn ($query, TenseTime $time) => $query->where('time', $time))
            ->orderBy('order')
            ->get()
            ->map(fn (Tense $tense) => [
                'id' => $tense->id,
                'key' => $tense->key->value,
                'name' => $tense->name,
                'time' => $tense->time->value,
                'aspect' => $tense->aspect->value,
                'summary' => $tense->summary,
            ]);

        return Inertia::render('tenses/index', [
            'times' => array_map(
                fn (TenseTime $time) => ['value' => $time->value, 'label' => ucfirst($time->value)],
                TenseTime::cases(),
            ),
            'filters' => [
                'time' => $time?->value,
            ],
            'tenses' => $tenses,
        ]);
    }

    /**
     * Show a single tense's full detail: structure, usage rules, signal
     * words, examples, and common confusions.
     */
    public function show(Tense $tense): Response
    {
        return Inertia::render('tenses/show', [
            'tense' => [
                'id' => $tense->id,
                'key' => $tense->key->value,
                'name' => $tense->name,
                'time' => $tense->time->value,
                'aspect' => $tense->aspect->value,
                'summary' => $tense->summary,
                'structure_affirmative' => $tense->structure_affirmative,
                'structure_negative' => $tense->structure_negative,
                'structure_interrogative' => $tense->structure_interrogative,
                'usage_rules' => $tense->usage_rules,
                'signal_words' => $tense->signal_words,
                'examples' => $tense->examples,
                'common_confusions' => $tense->common_confusions,
            ],
        ]);
    }
}
