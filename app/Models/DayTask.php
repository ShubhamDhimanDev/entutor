<?php

namespace App\Models;

use App\Enums\SkillArea;
use Database\Factories\DayTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $day_id
 * @property SkillArea $type
 * @property string $content
 * @property int $estimated_minutes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Day $day
 * @property-read Collection<int, DayTaskVocabularyItem> $vocabularyItems
 * @property-read Collection<int, LearnerDayTask> $learnerDayTasks
 */
#[Fillable(['day_id', 'type', 'content', 'estimated_minutes'])]
class DayTask extends Model
{
    /** @use HasFactory<DayTaskFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SkillArea::class,
            'estimated_minutes' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Day, $this>
     */
    public function day(): BelongsTo
    {
        return $this->belongsTo(Day::class);
    }

    /**
     * @return HasMany<DayTaskVocabularyItem, $this>
     */
    public function vocabularyItems(): HasMany
    {
        return $this->hasMany(DayTaskVocabularyItem::class);
    }

    /**
     * @return HasMany<LearnerDayTask, $this>
     */
    public function learnerDayTasks(): HasMany
    {
        return $this->hasMany(LearnerDayTask::class);
    }
}
