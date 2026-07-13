<?php

use Illuminate\Support\Facades\Route;
use Modules\Orientamento\Http\Controllers\OrientamentoController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('orientamentos', OrientamentoController::class)->names('orientamento');
});
