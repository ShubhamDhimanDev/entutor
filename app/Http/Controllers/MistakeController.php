<?php

namespace App\Http\Controllers;

use App\Enums\MistakeTag;
use App\Http\Requests\Library\MistakeIndexRequest;
use App\Models\CommonMistake;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MistakeController extends Controller
{
    /**
     * Number of common mistakes shown per page.
     */
    private const PER_PAGE = 10;

    /**
     * Browse the global "Fix Common Mistakes" library: paginated, filterable
     * by mistake tag.
     */
    public function index(MistakeIndexRequest $request): Response
    {
        $filters = $request->validated();

        $tag = filled($filters['tag'] ?? null) ? MistakeTag::from((string) $filters['tag']) : null;

        $mistakes = CommonMistake::query()
            ->when($tag, fn ($query, MistakeTag $tag) => $query->where('tag', $tag))
            ->orderBy('order')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn (CommonMistake $mistake) => [
                'id' => $mistake->id,
                'wrong_sentence' => $mistake->wrong_sentence,
                'corrected_sentence' => $mistake->corrected_sentence,
                'explanation' => $mistake->explanation,
                'tag' => $mistake->tag->value,
            ]);

        return Inertia::render('mistakes/index', [
            'tags' => array_map(
                fn (MistakeTag $tag) => ['value' => $tag->value, 'label' => Str::headline($tag->value)],
                MistakeTag::cases(),
            ),
            'filters' => [
                'tag' => $tag?->value,
            ],
            'mistakes' => $mistakes,
        ]);
    }
}
