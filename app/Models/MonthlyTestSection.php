<?php

namespace App\Models;

use App\Enums\SkillArea;
use Database\Factories\MonthlyTestSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $monthly_test_id
 * @property SkillArea $skill
 * @property int $weight
 * @property string $content
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read MonthlyTest $monthlyTest
 */
#[Fillable(['monthly_test_id', 'skill', 'weight', 'content', 'order'])]
class MonthlyTestSection extends Model
{
    /** @use HasFactory<MonthlyTestSectionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'skill' => SkillArea::class,
            'weight' => 'integer',
            'order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<MonthlyTest, $this>
     */
    public function monthlyTest(): BelongsTo
    {
        return $this->belongsTo(MonthlyTest::class);
    }
}
