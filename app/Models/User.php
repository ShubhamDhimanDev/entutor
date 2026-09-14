<?php

namespace App\Models;

use App\Actions\IssueEmailOtp;
use App\Enums\CoachLinkStatus;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property UserRole $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MustVerifyEmailTrait, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Send the email verification notification via a one-time code.
     */
    public function sendEmailVerificationNotification(): void
    {
        app(IssueEmailOtp::class)->handle($this);
    }

    /**
     * @return HasOne<LearnerProgram, $this>
     */
    public function learnerProgram(): HasOne
    {
        return $this->hasOne(LearnerProgram::class);
    }

    /**
     * @return HasOne<CoachLink, $this>
     */
    public function coachLinkAsLearner(): HasOne
    {
        return $this->hasOne(CoachLink::class, 'learner_user_id')
            ->where('status', CoachLinkStatus::Active);
    }

    /**
     * @return HasMany<CoachLink, $this>
     */
    public function coachLinksAsCoach(): HasMany
    {
        return $this->hasMany(CoachLink::class, 'coach_user_id')
            ->where('status', CoachLinkStatus::Active);
    }
}
