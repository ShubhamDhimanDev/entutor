<?php

namespace App\Models;

use App\Enums\MistakeTag;
use Database\Factories\CommonMistakeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $wrong_sentence
 * @property string $corrected_sentence
 * @property string $explanation
 * @property MistakeTag $tag
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['wrong_sentence', 'corrected_sentence', 'explanation', 'tag', 'order'])]
class CommonMistake extends Model
{
    /** @use HasFactory<CommonMistakeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tag' => MistakeTag::class,
            'order' => 'integer',
        ];
    }
}
