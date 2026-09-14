<?php

namespace App\Http\Requests;

use App\Enums\SkillArea;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMonthlyTestRecordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'taken_on' => ['nullable', 'date'],
            'total_score' => ['required', 'integer', 'between:0,100'],
            'scores' => ['nullable', 'array'],
            'scores.*.skill' => ['required', Rule::enum(SkillArea::class)],
            'scores.*.score' => ['required', 'integer', 'between:0,100'],
        ];
    }
}
