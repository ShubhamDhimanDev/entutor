<?php

namespace App\Models;

use Database\Factories\ConfidenceQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $confidence_topic_id
 * @property string $question
 * @property string $example_answer
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ConfidenceTopic $topic
 */
#[Fillable(['confidence_topic_id', 'question', 'example_answer', 'order'])]
class ConfidenceQuestion extends Model
{
    /** @use HasFactory<ConfidenceQuestionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ConfidenceTopic, $this>
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ConfidenceTopic::class, 'confidence_topic_id');
    }
}
