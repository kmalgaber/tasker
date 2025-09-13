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
     * @return mixed
     */
    public function execute(array $data): LengthAwarePaginator
    {
        return User::query()
            ->whereLike('name', '%'.$data['keyword'].'%')
            ->orWhereLike('email', '%'.$data['keyword'].'%')
            ->paginate();
    }
}
