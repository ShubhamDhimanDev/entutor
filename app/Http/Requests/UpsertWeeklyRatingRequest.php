<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpsertWeeklyRatingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reading_rating' => ['required', 'integer', 'between:1,5'],
            'speaking_rating' => ['required', 'integer', 'between:1,5'],
            'listening_rating' => ['required', 'integer', 'between:1,5'],
            'confidence_rating' => ['required', 'integer', 'between:1,5'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
