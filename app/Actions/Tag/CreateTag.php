<?php

namespace App\Actions\Tag;

use App\Models\Tag;
use Spatie\QueueableAction\QueueableAction;

class CreateTag
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
     * @param array $data
     *
     * @return Tag
     */
    public function execute(array $data): Tag
    {
        return Tag::query()->create($data);
    }
}
