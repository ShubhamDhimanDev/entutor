<?php

namespace App\Models;

use Database\Factories\WordBankGroupFactory;
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
 * @property-read Collection<int, WordBankEntry> $entries
 */
#[Fillable(['name', 'order'])]
class WordBankGroup extends Model
{
    /** @use HasFactory<WordBankGroupFactory> */
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
     * @return HasMany<WordBankEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(WordBankEntry::class);
    }
}
