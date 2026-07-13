<?php

use Illuminate\Support\Facades\Route;
use Modules\Filigrana\Http\Controllers\FiligranaController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('filigranas', FiligranaController::class)->names('filigrana');
});
