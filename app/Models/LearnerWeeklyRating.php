<?php

namespace App\Models;

use Database\Factories\LearnerWeeklyRatingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $learner_program_id
 * @property int $week_id
 * @property int $reading_rating
 * @property int $speaking_rating
 * @property int $listening_rating
 * @property int $confidence_rating
 * @property string|null $note
 * @property int|null $rated_by_user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read LearnerProgram $learnerProgram
 * @property-read Week $week
 * @property-read User|null $ratedBy
 */
#[Fillable([
    'learner_program_id',
    'week_id',
    'reading_rating',
    'speaking_rating',
    'listening_rating',
    'confidence_rating',
    'note',
    'rated_by_user_id',
])]
class LearnerWeeklyRating extends Model
{
    /** @use HasFactory<LearnerWeeklyRatingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reading_rating' => 'integer',
            'speaking_rating' => 'integer',
            'listening_rating' => 'integer',
            'confidence_rating' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<LearnerProgram, $this>
     */
    public function learnerProgram(): BelongsTo
    {
        return $this->belongsTo(LearnerProgram::class);
    }

    /**
     * @return BelongsTo<Week, $this>
     */
    public function week(): BelongsTo
    {
        return $this->belongsTo(Week::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function ratedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_by_user_id');
    }
}
