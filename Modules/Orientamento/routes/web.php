<?php

use Illuminate\Support\Facades\Route;
use Modules\Orientamento\Http\Controllers\OrientamentoController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('orientamentos', OrientamentoController::class)->names('orientamento');
});
