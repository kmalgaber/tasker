<?php

namespace App\Http\Requests\V1\Tag;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** @schemaName UpdateTagRequest */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /** @example #1289ef */
            'color' => 'required|hex_color',
        ];
    }
}
