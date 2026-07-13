<?php

use Illuminate\Support\Facades\Route;
use Modules\Eventi\Http\Controllers\EventiController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('eventis', EventiController::class)->names('eventi');
});
