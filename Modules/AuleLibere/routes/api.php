<?php

use Illuminate\Support\Facades\Route;
use Modules\AuleLibere\Http\Controllers\AuleLibereController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('auleliberes', AuleLibereController::class)->names('aulelibere');
});
