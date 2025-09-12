<?php

namespace App\Actions\Tag;

use App\Models\Tag;
use Spatie\QueueableAction\QueueableAction;

class UpdateTag
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
     * @param Tag $tag
     *
     * @return Tag
     */
    public function execute(array $data, Tag $tag): Tag
    {
        $tag->update($data);

        return $tag;
    }
}
