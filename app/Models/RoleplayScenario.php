<?php

namespace App\Models;

use App\Enums\RoleplayDomain;
use Database\Factories\RoleplayScenarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property RoleplayDomain $domain
 * @property string $tip
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, RoleplayLine> $lines
 */
#[Fillable(['title', 'domain', 'tip', 'order'])]
class RoleplayScenario extends Model
{
    /** @use HasFactory<RoleplayScenarioFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'domain' => RoleplayDomain::class,
            'order' => 'integer',
        ];
    }

    /**
     * @return HasMany<RoleplayLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(RoleplayLine::class);
    }
}
