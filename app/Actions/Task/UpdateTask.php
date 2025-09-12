<?php

namespace App\Actions\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Tag;
use App\Models\Task;
use Spatie\QueueableAction\QueueableAction;

class UpdateTask
{
    use QueueableAction;

    /**
     * Create a new action instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Prepare the action for execution, leveraging constructor injection.
    }

    /**
     * Execute the action.
     *
     * @param Task $task
     * @param array $data
     *
     * @return Task
     */
    public function execute(Task $task, array $data): Task
    {
        if (!array_key_exists('status', $data)) {
            $data['status'] = TaskStatus::Pending;
        }
        if (!array_key_exists('priority', $data)) {
            $data['priority'] = TaskPriority::Medium;
        }
        $data['user_id'] = auth()->id();

        $task->update($this->filterAttributes($data));

        if (array_key_exists('tags', $data)) {
            $tags = Tag::query()->whereIn('name', $data['tags'])->get();
            $task->tags()->sync($tags);
        }

        return $task;
    }

    private function filterAttributes(array $data): array
    {
        $nonAttributes = array_flip([
            'tags',
        ]);
        return array_filter($data, fn($p) => !isset($nonAttributes[$p]), ARRAY_FILTER_USE_KEY);
    }
}
