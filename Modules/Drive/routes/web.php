<?php

use Illuminate\Support\Facades\Route;
use Modules\Drive\Http\Controllers\DriveController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('drives', DriveController::class)->names('drive');
});
