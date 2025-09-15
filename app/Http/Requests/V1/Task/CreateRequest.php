<?php

namespace App\Http\Requests\V1\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** @schemaName CreateTaskRequest */
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
            'title' => 'required|string|min:5|max:120',
            'description' => 'string',
            'status' => [Rule::enum(TaskStatus::class)],
            'priority' => [Rule::enum(TaskPriority::class)],
            'due_date' => 'date_format:Y-m-d|after_or_equal:today',
            'assignee_id' => 'uuid|exists:users,id',
            'tags' => 'array|list',
            'tags.*' => 'exists:tags,name',
            'metadata' => 'array',
        ];
    }
}
