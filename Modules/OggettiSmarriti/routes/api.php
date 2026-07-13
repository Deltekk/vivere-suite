<?php

use Illuminate\Support\Facades\Route;
use Modules\OggettiSmarriti\Http\Controllers\OggettiSmarritiController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('oggettismarritis', OggettiSmarritiController::class)->names('oggettismarriti');
});
