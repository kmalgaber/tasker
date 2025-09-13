<?php

namespace App\Actions\Task;

use App\Models\Tag;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Spatie\QueueableAction\QueueableAction;
use Throwable;

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
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function execute(Task $task, array $data): Task
    {
        DB::transaction(function () use ($task, $data) {
            $task->update($this->filterAttributes($data));

            if (array_key_exists('tags', $data)) {
                $tags = Tag::query()->whereIn('name', $data['tags'])->get();
                $task->tags()->sync($tags);
            }
        });

        return $task;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function filterAttributes(array $data): array
    {
        $nonAttributes = array_flip([
            'tags',
        ]);

        return array_filter($data, fn ($p) => ! isset($nonAttributes[$p]), ARRAY_FILTER_USE_KEY);
    }
}
