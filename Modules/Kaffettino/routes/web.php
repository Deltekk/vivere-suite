<?php

use Illuminate\Support\Facades\Route;
use Modules\Kaffettino\Http\Controllers\KaffettinoController;

/* Per renderle solo in auth allora usare il middleware, tipo
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('kaffettino', KaffettinoController::class)->names('kaffettino');
});
*/

Route::middleware(['web'])->group(function () {
    Route::resource('kaffettino', KaffettinoController::class)->names('kaffettino');
});

