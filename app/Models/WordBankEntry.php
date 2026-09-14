<?php

namespace App\Models;

use Database\Factories\WordBankEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $word_bank_group_id
 * @property string $term
 * @property string $native_language
 * @property string $native_meaning
 * @property string $native_transliteration
 * @property string $example_sentence
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read WordBankGroup $group
 * @property-read Collection<int, DayTaskVocabularyItem> $dayTaskVocabularyItems
 */
#[Fillable([
    'word_bank_group_id',
    'term',
    'native_language',
    'native_meaning',
    'native_transliteration',
    'example_sentence',
    'order',
])]
class WordBankEntry extends Model
{
    /** @use HasFactory<WordBankEntryFactory> */
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
     * @return BelongsTo<WordBankGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(WordBankGroup::class, 'word_bank_group_id');
    }

    /**
     * @return HasMany<DayTaskVocabularyItem, $this>
     */
    public function dayTaskVocabularyItems(): HasMany
    {
        return $this->hasMany(DayTaskVocabularyItem::class);
    }
}
