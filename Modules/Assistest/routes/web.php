<?php

use Illuminate\Support\Facades\Route;
use Modules\Assistest\Http\Controllers\AssistestController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('assistests', AssistestController::class)->names('assistest');
});
