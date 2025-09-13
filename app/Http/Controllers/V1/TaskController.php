<?php

namespace App\Http\Controllers\V1;

use App\Actions\Task\CreateTask;
use App\Actions\Task\SearchTask;
use App\Actions\Task\UpdateTask;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Task\CreateRequest;
use App\Http\Requests\V1\Task\UpdateRequest;
use App\Http\Resources\V1\TaskResource;
use App\Models\Task;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    #[QueryParameter(name: 'page', type: 'integer')]
    #[QueryParameter(
        name: 'filter[field]',
        description: 'Supported: status, priority, assignee_id, due_date_before, due_date_after, tags.name',
        type: 'string',
        example: 'filter[status]=completed'
    )]
    #[QueryParameter(
        name: 'sort',
        description: "Put '-' in front of the field name for reverse ordering",
        type: 'string',
        example: '-title'
    )]
    public function index(SearchTask $action): ResourceCollection
    {
        Gate::authorize('viewAny', Task::class);

        return TaskResource::collection($action->execute());
    }

    public function store(CreateRequest $request, CreateTask $action): TaskResource
    {
        Gate::authorize('create', Task::class);

        $task = $action->execute($request->validated());

        return new TaskResource($task);
    }

    public function show(Task $task): TaskResource
    {
        Gate::authorize('view', $task);

        return new TaskResource($task);
    }

    public function update(UpdateRequest $request, Task $task, UpdateTask $action): TaskResource
    {
        Gate::authorize('update', $task);

        $task = $action->execute($task, $request->validated());

        return new TaskResource($task);
    }

    public function destroy(Task $task): Response
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }
}
