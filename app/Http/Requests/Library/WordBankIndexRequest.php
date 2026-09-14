<?php

namespace App\Http\Requests\Library;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WordBankIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Word Bank content is global reference material, not scoped to any
     * user or role — any authenticated (and verified) user may browse it,
     * which the route's middleware already enforces.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'group' => ['nullable', 'integer', 'exists:word_bank_groups,id'],
        ];
    }
}
