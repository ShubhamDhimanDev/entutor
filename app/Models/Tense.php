<?php

namespace App\Models;

use App\Enums\TenseAspect;
use App\Enums\TenseKey;
use App\Enums\TenseTime;
use Database\Factories\TenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property TenseKey $key
 * @property string $name
 * @property TenseTime $time
 * @property TenseAspect $aspect
 * @property string $summary
 * @property string $structure_affirmative
 * @property string $structure_negative
 * @property string $structure_interrogative
 * @property array<int, string> $usage_rules
 * @property array<int, string> $signal_words
 * @property array<int, array{sentence: string, native_meaning: string}> $examples
 * @property string|null $common_confusions
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, DayTask> $dayTasks
 */
#[Fillable([
    'key',
    'name',
    'time',
    'aspect',
    'summary',
    'structure_affirmative',
    'structure_negative',
    'structure_interrogative',
    'usage_rules',
    'signal_words',
    'examples',
    'common_confusions',
    'order',
])]
class Tense extends Model
{
    /** @use HasFactory<TenseFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'key' => TenseKey::class,
            'time' => TenseTime::class,
            'aspect' => TenseAspect::class,
            'usage_rules' => 'array',
            'signal_words' => 'array',
            'examples' => 'array',
            'order' => 'integer',
        ];
    }

    /**
     * @return HasMany<DayTask, $this>
     */
    public function dayTasks(): HasMany
    {
        return $this->hasMany(DayTask::class);
    }
}
