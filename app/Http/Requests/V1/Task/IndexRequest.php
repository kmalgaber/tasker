<?php

namespace App\Http\Requests\V1\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'filter.text' => 'string',
            'filter.status' => [Rule::enum(TaskStatus::class)],
            'filter.priority' => [Rule::enum(TaskPriority::class)],
            'filter.assignee_id' => 'exists:users,id', 'filter.due_date_before' => [
                'nullable', 'date',
                Rule::when($this->filled('filter.due_date_after'), 'after_or_equal:filter.due_date_after'),
            ],
            'filter.due_date_after' => [
                'nullable', 'date',
                Rule::when($this->filled('filter.due_date_before'), 'before_or_equal:filter.due_date_before'),
            ],
            'filter.tags\.name' => 'exists:tags,name',
            'sort' => 'string',
        ];
    }
}
