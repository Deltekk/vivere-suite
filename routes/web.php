<?php

use Illuminate\Support\Facades\Route;

// La home della suite è la piattaforma HR (panel Filament con path "hr")
Route::redirect('/', '/hr')->name('home');
