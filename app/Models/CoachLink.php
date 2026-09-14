<?php

namespace App\Models;

use App\Enums\CoachLinkStatus;
use Database\Factories\CoachLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $learner_user_id
 * @property int|null $coach_user_id
 * @property string $invite_code
 * @property Carbon|null $invite_code_expires_at
 * @property CoachLinkStatus $status
 * @property Carbon|null $redeemed_at
 * @property Carbon|null $revoked_at
 * @property int|null $revoked_by_user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $learner
 * @property-read User|null $coach
 * @property-read User|null $revokedBy
 */
#[Fillable([
    'learner_user_id',
    'coach_user_id',
    'invite_code',
    'invite_code_expires_at',
    'status',
    'redeemed_at',
    'revoked_at',
    'revoked_by_user_id',
])]
class CoachLink extends Model
{
    /** @use HasFactory<CoachLinkFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invite_code_expires_at' => 'datetime',
            'status' => CoachLinkStatus::class,
            'redeemed_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function learner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'learner_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }
}
