<?php

use Illuminate\Support\Facades\Route;
use Modules\Segnalazioni\Http\Controllers\SegnalazioniController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('segnalazionis', SegnalazioniController::class)->names('segnalazioni');
});
