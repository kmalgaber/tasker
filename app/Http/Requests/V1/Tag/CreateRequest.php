<?php

namespace App\Http\Requests\V1\Tag;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** @schemaName CreateTagRequest */
class CreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:12',
            /** @example #1289ef */
            'color' => 'hex_color',
        ];
    }
}
