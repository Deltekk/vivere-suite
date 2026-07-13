<?php

use Illuminate\Support\Facades\Route;
use Modules\ProblemiTecnici\Http\Controllers\ProblemiTecniciController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('problemitecnicis', ProblemiTecniciController::class)->names('problemitecnici');
});
