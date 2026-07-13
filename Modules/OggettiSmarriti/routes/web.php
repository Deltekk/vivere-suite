<?php

use Illuminate\Support\Facades\Route;
use Modules\OggettiSmarriti\Http\Controllers\OggettiSmarritiController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('oggettismarritis', OggettiSmarritiController::class)->names('oggettismarriti');
});
