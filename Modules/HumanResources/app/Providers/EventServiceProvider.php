<?php

namespace Modules\HumanResources\Providers;

use Filament\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\HumanResources\Listeners\MarkEmailVerifiedOnPasswordReset;
use Modules\HumanResources\Listeners\NotifyRegistrationReviewers;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        // Nuova registrazione dal panel HR: avvisa gli admin del corso
        Registered::class => [NotifyRegistrationReviewers::class],
        // Il link di reset password dimostra che la mail è dell'utente
        PasswordReset::class => [MarkEmailVerifiedOnPasswordReset::class],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * Disattivato: la discovery di Laravel guarda app/Listeners della root, non il modulo,
     * e rischierebbe di registrare due volte gli stessi listener. I listener del modulo
     * sono elencati qui sopra.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = false;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
