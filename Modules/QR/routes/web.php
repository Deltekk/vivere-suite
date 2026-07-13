<?php

use Illuminate\Support\Facades\Route;
use Modules\QR\Http\Controllers\QRController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('qrs', QRController::class)->names('qr');
});
