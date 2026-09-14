<?php

namespace App\Http\Controllers;

use App\Http\Requests\Library\WordBankIndexRequest;
use App\Models\WordBankEntry;
use App\Models\WordBankGroup;
use Inertia\Inertia;
use Inertia\Response;

class WordBankController extends Controller
{
    /**
     * Browse the global Word Bank: searchable, filterable by group, paginated
     * 25-per-page (matching the seeded group size).
     */
    public function index(WordBankIndexRequest $request): Response
    {
        $filters = $request->validated();

        $search = filled($filters['q'] ?? null) ? (string) $filters['q'] : null;
        $groupId = filled($filters['group'] ?? null) ? (int) $filters['group'] : null;

        $entries = WordBankEntry::query()
            ->with('group')
            ->when($groupId, fn ($query, int $groupId) => $query->where('word_bank_group_id', $groupId))
            ->when($search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('term', 'like', "%{$search}%")
                        ->orWhere('native_meaning', 'like', "%{$search}%")
                        ->orWhere('example_sentence', 'like', "%{$search}%");
                });
            })
            ->orderBy('word_bank_group_id')
            ->orderBy('order')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (WordBankEntry $entry) => [
                'id' => $entry->id,
                'term' => $entry->term,
                'native_meaning' => $entry->native_meaning,
                'native_transliteration' => $entry->native_transliteration,
                'example_sentence' => $entry->example_sentence,
                'group' => [
                    'id' => $entry->group->id,
                    'name' => $entry->group->name,
                ],
            ]);

        $groups = WordBankGroup::query()
            ->orderBy('order')
            ->get(['id', 'name'])
            ->map(fn (WordBankGroup $group) => [
                'id' => $group->id,
                'name' => $group->name,
            ]);

        return Inertia::render('word-bank/index', [
            'groups' => $groups,
            'filters' => [
                'q' => $search,
                'group' => $groupId,
            ],
            'entries' => $entries,
        ]);
    }
}
