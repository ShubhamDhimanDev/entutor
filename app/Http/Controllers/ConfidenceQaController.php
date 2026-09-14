<?php

namespace App\Http\Controllers;

use App\Models\ConfidenceQuestion;
use App\Models\ConfidenceTopic;
use Inertia\Inertia;
use Inertia\Response;

class ConfidenceQaController extends Controller
{
    /**
     * Browse the global Confidence Q&A library, grouped by topic.
     *
     * Small, fixed-size dataset (6 topics / 35 questions total) — returned
     * in one payload rather than paginated.
     */
    public function index(): Response
    {
        $topics = ConfidenceTopic::query()
            ->with(['questions' => fn ($query) => $query->orderBy('order')])
            ->orderBy('order')
            ->get()
            ->map(fn (ConfidenceTopic $topic) => [
                'id' => $topic->id,
                'name' => $topic->name,
                'questions' => $topic->questions->map(fn (ConfidenceQuestion $question) => [
                    'id' => $question->id,
                    'question' => $question->question,
                    'example_answer' => $question->example_answer,
                ]),
            ]);

        return Inertia::render('confidence-qa/index', [
            'topics' => $topics,
        ]);
    }
}
