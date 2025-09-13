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
            'filter.assignee_id' => 'uuid|exists:users,id',
            'filter.due_date_before' => [
                'date',
                Rule::when($this->filled('filter.due_date_after'), 'after_or_equal:filter.due_date_after'),
            ],
            'filter.due_date_after' => [
                'date',
                Rule::when($this->filled('filter.due_date_before'), 'before_or_equal:filter.due_date_before'),
            ],
            'filter.tags.*' => 'exists:tags,name',
            /** Admins can request for deleted records */
            'filter.trashed' => 'in:only,with',
            'sort' => 'in:title,-title,due_date,-due_date,created_at,-created_at',
            'page' => 'integer',
            'per_page' => 'integer',
        ];
    }
}
