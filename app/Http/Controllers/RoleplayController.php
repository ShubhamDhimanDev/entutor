<?php

namespace App\Http\Controllers;

use App\Enums\RoleplayDomain;
use App\Http\Requests\Library\RoleplayIndexRequest;
use App\Models\RoleplayLine;
use App\Models\RoleplayScenario;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RoleplayController extends Controller
{
    /**
     * Browse the global Roleplay Scenario library as a list (title, domain,
     * tip preview) — not the full dialogue, filterable by domain.
     */
    public function index(RoleplayIndexRequest $request): Response
    {
        $filters = $request->validated();

        $domain = filled($filters['domain'] ?? null) ? RoleplayDomain::from((string) $filters['domain']) : null;

        $scenarios = RoleplayScenario::query()
            ->when($domain, fn ($query, RoleplayDomain $domain) => $query->where('domain', $domain))
            ->orderBy('order')
            ->get()
            ->map(fn (RoleplayScenario $scenario) => [
                'id' => $scenario->id,
                'title' => $scenario->title,
                'domain' => $scenario->domain->value,
                'tip_preview' => Str::limit($scenario->tip, 120),
            ]);

        return Inertia::render('roleplay/index', [
            'domains' => array_map(
                fn (RoleplayDomain $domain) => ['value' => $domain->value, 'label' => Str::headline($domain->value)],
                RoleplayDomain::cases(),
            ),
            'filters' => [
                'domain' => $domain?->value,
            ],
            'scenarios' => $scenarios,
        ]);
    }

    /**
     * Show a single roleplay scenario's full ordered dialogue and tip.
     */
    public function show(RoleplayScenario $roleplayScenario): Response
    {
        $roleplayScenario->load(['lines' => fn ($query) => $query->orderBy('order')]);

        return Inertia::render('roleplay/show', [
            'scenario' => [
                'id' => $roleplayScenario->id,
                'title' => $roleplayScenario->title,
                'domain' => $roleplayScenario->domain->value,
                'domain_label' => Str::headline($roleplayScenario->domain->value),
                'tip' => $roleplayScenario->tip,
                'lines' => $roleplayScenario->lines->map(fn (RoleplayLine $line) => [
                    'id' => $line->id,
                    'side' => $line->side->value,
                    'speaker_label' => $line->speaker_label,
                    'line_text' => $line->line_text,
                ]),
            ],
        ]);
    }
}
