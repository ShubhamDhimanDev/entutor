<?php

namespace App\Models;

use Database\Factories\ConfidenceTopicFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ConfidenceQuestion> $questions
 */
#[Fillable(['name', 'order'])]
class ConfidenceTopic extends Model
{
    /** @use HasFactory<ConfidenceTopicFactory> */
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
     * @return HasMany<ConfidenceQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(ConfidenceQuestion::class);
    }
}
