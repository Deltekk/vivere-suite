<?php

use Illuminate\Support\Facades\Route;
use Modules\Assistest\Http\Controllers\AssistestController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('assistests', AssistestController::class)->names('assistest');
});
