<?php

namespace App\Http\Requests\V1\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'string|min:5',
            'description' => 'string',
            'status' => [Rule::enum(TaskStatus::class)],
            'priority' => [Rule::enum(TaskPriority::class)],
            'due_date' => 'date_format:Y-m-d|after:today',
            'assignee_id' => 'uuid|exists:users,id',
            'tags' => 'array|list',
            'tags.*' => 'exists:tags,name',
            'metadata' => 'array',
        ];
    }
}
