<?php

namespace App\Models;

use App\Enums\RoleplaySide;
use Database\Factories\RoleplayLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $roleplay_scenario_id
 * @property RoleplaySide $side
 * @property string $speaker_label
 * @property string $line_text
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read RoleplayScenario $scenario
 */
#[Fillable(['roleplay_scenario_id', 'side', 'speaker_label', 'line_text', 'order'])]
class RoleplayLine extends Model
{
    /** @use HasFactory<RoleplayLineFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'side' => RoleplaySide::class,
            'order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<RoleplayScenario, $this>
     */
    public function scenario(): BelongsTo
    {
        return $this->belongsTo(RoleplayScenario::class, 'roleplay_scenario_id');
    }
}
