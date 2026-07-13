<?php

use Illuminate\Support\Facades\Route;
use Modules\Segnalazioni\Http\Controllers\SegnalazioniController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('segnalazionis', SegnalazioniController::class)->names('segnalazioni');
});
