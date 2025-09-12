<?php

namespace App\Actions\Task;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueueableAction\QueueableAction;

class SearchTask
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
     * @return mixed
     */
    public function execute(): LengthAwarePaginator
    {
        return QueryBuilder::for(Task::class)
            ->with(['user', 'assignee', 'tags'])
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('priority'),
                AllowedFilter::exact('assignee_id'),
                AllowedFilter::exact('tags.name'),
                AllowedFilter::scope('due_date_before'),
                AllowedFilter::scope('due_date_after'),
            ])
            ->defaultSort('created_at')
            ->allowedSorts('created_at', 'due_date', 'title')
            ->paginate();
    }
}
