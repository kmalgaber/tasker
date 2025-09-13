<?php

namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        activity()->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties($task->getAttributes())
            ->event('created')
            ->log('task created');
    }

    public function updating(Task $task): bool
    {
        if (Task::query()->where('id', $task->getKey())->select('version')->first()?->version != $task->version) {
            return false;
        }
        $task->version += 1;

        return true;
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        activity()->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties($task->getChanges())
            ->event('updated')
            ->log('task updated');
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        activity()->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties($task->getAttributes())
            ->event('deleted')
            ->log('task deleted');
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        activity()->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties($task->getAttributes())
            ->event('restored')
            ->log('task restored');
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        activity()->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties($task->getAttributes())
            ->event('force_deleted')
            ->log('task force deleted');
    }
}
