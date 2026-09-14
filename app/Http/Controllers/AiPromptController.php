<?php

namespace App\Http\Controllers;

use App\Models\AiPrompt;
use App\Models\AiPromptCategory;
use Inertia\Inertia;
use Inertia\Response;

class AiPromptController extends Controller
{
    /**
     * Browse the global AI Practice Prompt library, grouped by category.
     *
     * Small, fixed-size dataset (6 categories / 30 prompts total) — returned
     * in one payload rather than paginated.
     */
    public function index(): Response
    {
        $categories = AiPromptCategory::query()
            ->with(['prompts' => fn ($query) => $query->orderBy('order')])
            ->orderBy('order')
            ->get()
            ->map(fn (AiPromptCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'prompts' => $category->prompts->map(fn (AiPrompt $prompt) => [
                    'id' => $prompt->id,
                    'prompt_text' => $prompt->prompt_text,
                ]),
            ]);

        return Inertia::render('ai-prompts/index', [
            'categories' => $categories,
        ]);
    }
}
