<?php

namespace App\Models;

use Database\Factories\AiPromptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ai_prompt_category_id
 * @property string $prompt_text
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AiPromptCategory $category
 */
#[Fillable(['ai_prompt_category_id', 'prompt_text', 'order'])]
class AiPrompt extends Model
{
    /** @use HasFactory<AiPromptFactory> */
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
     * @return BelongsTo<AiPromptCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AiPromptCategory::class, 'ai_prompt_category_id');
    }
}
