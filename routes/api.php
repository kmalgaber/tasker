<?php

use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth')->group(function () {
    Route::resource('tasks', TaskController::class);
    Route::resource('tags', TagController::class);
});
