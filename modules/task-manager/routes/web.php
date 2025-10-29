<?php

use Illuminate\Support\Facades\Route;
use Modules\TaskManager\Http\Controllers\TaskManagerController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('taskmanagers', TaskManagerController::class)->names('taskmanager');
});
