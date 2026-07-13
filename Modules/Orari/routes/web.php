<?php

use Illuminate\Support\Facades\Route;
use Modules\Orari\Http\Controllers\OrariController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('oraris', OrariController::class)->names('orari');
});
