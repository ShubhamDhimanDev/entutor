<?php

namespace App\Http\Requests\Library;

use App\Enums\MistakeTag;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class MistakeIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Common Mistakes content is global reference material, not scoped to
     * any user or role — any authenticated (and verified) user may browse
     * it, which the route's middleware already enforces.
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
            'tag' => ['nullable', new Enum(MistakeTag::class)],
        ];
    }
}
