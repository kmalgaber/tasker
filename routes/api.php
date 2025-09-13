<?php

use App\Http\Controllers\V1\TagController;
use App\Http\Controllers\V1\TaskController;
use App\Http\Controllers\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('tasks', TaskController::class);
    Route::resource('tags', TagController::class);
});

// withTrashed() does not work when the route is defined inside the group
Route::middleware('auth')->patch('/v1/tasks/{task}', [TaskController::class, 'restore'])->withTrashed();
