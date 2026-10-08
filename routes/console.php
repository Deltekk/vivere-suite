<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ----- Job schedulati della suite (in sviluppo li esegue "schedule:work" dentro composer dev) -----

// Audit log: cancella le voci più vecchie del periodo di conservazione (D13, 2 anni)
Schedule::command('activitylog:clean --force')->daily();
