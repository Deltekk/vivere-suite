<?php

use Illuminate\Support\Facades\Route;
use Modules\AuleLibere\Http\Controllers\AuleLibereController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('auleliberes', AuleLibereController::class)->names('aulelibere');
});
