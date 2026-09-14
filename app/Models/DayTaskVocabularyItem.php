<?php

namespace App\Models;

use Database\Factories\DayTaskVocabularyItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $day_task_id
 * @property int|null $word_bank_entry_id
 * @property string $term
 * @property string $native_meaning
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DayTask $dayTask
 * @property-read WordBankEntry|null $wordBankEntry
 */
#[Fillable(['day_task_id', 'word_bank_entry_id', 'term', 'native_meaning', 'order'])]
class DayTaskVocabularyItem extends Model
{
    /** @use HasFactory<DayTaskVocabularyItemFactory> */
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
     * @return BelongsTo<DayTask, $this>
     */
    public function dayTask(): BelongsTo
    {
        return $this->belongsTo(DayTask::class);
    }

    /**
     * @return BelongsTo<WordBankEntry, $this>
     */
    public function wordBankEntry(): BelongsTo
    {
        return $this->belongsTo(WordBankEntry::class);
    }
}
