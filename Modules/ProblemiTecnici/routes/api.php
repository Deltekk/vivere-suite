<?php

use Illuminate\Support\Facades\Route;
use Modules\ProblemiTecnici\Http\Controllers\ProblemiTecniciController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('problemitecnicis', ProblemiTecniciController::class)->names('problemitecnici');
});
