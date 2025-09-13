<?php

namespace App\Http\Controllers\V1;

use App\Actions\Task\CreateTask;
use App\Actions\Task\SearchTask;
use App\Actions\Task\UpdateTask;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Task\CreateRequest;
use App\Http\Requests\V1\Task\IndexRequest;
use App\Http\Requests\V1\Task\UpdateRequest;
use App\Http\Resources\V1\TaskResource;
use App\Models\Task;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Throwable;

class TaskController extends Controller
{
    /**
     * List tasks in a paginated format
     *
     * Can accept filtering/sorting parameters.
     */
    public function index(IndexRequest $request, SearchTask $action): ResourceCollection
    {
        Gate::authorize('viewAny', Task::class);

        /** @var array{filter?:array<string, mixed>, sort?: string, page?: int} $data */
        $data = $request->validated();

        return TaskResource::collection($action->execute($data));
    }

    /**
     * Create a new task
     */
    public function store(CreateRequest $request, CreateTask $action): TaskResource
    {
        Gate::authorize('create', Task::class);

        $task = $action->execute($request->validated());

        return new TaskResource($task);
    }

    /**
     * Get the task in detail
     */
    public function show(Task $task): TaskResource
    {
        Gate::authorize('view', $task);

        return new TaskResource($task);
    }

    /**
     * Update the specified task
     */
    public function update(UpdateRequest $request, Task $task, UpdateTask $action): TaskResource
    {
        Gate::authorize('update', $task);

        try {
            $task = $action->execute($task, $request->validated());
        } catch (Throwable $e) {
            activity()
                ->causedBy(auth()->user())
                ->event('error')
                ->performedOn($task)
                ->withProperties($request->all())
                ->log($e);
        }

        return new TaskResource($task);
    }

    /**
     * Soft delete the task
     */
    public function destroy(Task $task): Response
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }

    /**
     * Restore the soft-deleted task
     *
     * Only for admins.
     */
    public function restore(Task $task): TaskResource
    {
        Gate::authorize('restore', $task);

        $task->restore();

        return new TaskResource($task);
    }
}
