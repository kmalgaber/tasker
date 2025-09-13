<?php

namespace App\Actions\Task;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
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
     * @param  array{filter?:array<string, mixed>, sort?: string, page?: int}  $data
     * @return LengthAwarePaginator<int, Task>
     */
    public function execute(array $data): LengthAwarePaginator
    {
        $allowedFilters = $this->buildAllowedFilters($data);

        $query = QueryBuilder::for(Task::class)
            ->allowedFilters($allowedFilters)
            ->defaultSort('created_at')
            ->allowedSorts($data['sort'] ?? [])
            ->with(['user', 'assignee', 'tags']);
        if (auth()->user()?->is_admin) {
            $query->withTrashed();
        }

        return $query->paginate();
    }

    /**
     * @param  array{filter?:array<string, mixed>}  $data
     * @return list<AllowedFilter>
     */
    private function buildAllowedFilters(array $data): array
    {
        return isset($data['filter']) ? array_map(
            fn ($key) => method_exists(Task::class, Str::camel($key)) ?
                AllowedFilter::scope($key) : AllowedFilter::exact($key),
            array_keys($data['filter'])
        ) : [];
    }
}
