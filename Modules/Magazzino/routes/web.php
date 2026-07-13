<?php

use Illuminate\Support\Facades\Route;
use Modules\Magazzino\Http\Controllers\MagazzinoController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('magazzinos', MagazzinoController::class)->names('magazzino');
});
