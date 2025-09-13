<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueueableAction\QueueableAction;

class SearchUser
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
     * @return LengthAwarePaginator<int, User>
     */
    public function execute(array $data): LengthAwarePaginator
    {
        return User::query()
            // @phpstan-ignore-next-line
            ->whereLike('name', '%'.$data['search'].'%')
            // @phpstan-ignore-next-line
            ->orWhereLike('email', '%'.$data['search'].'%')
            ->paginate();
    }
}
