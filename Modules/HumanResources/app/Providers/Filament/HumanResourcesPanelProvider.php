<?php

namespace Modules\HumanResources\Providers\Filament;

use App\Providers\Filament\VivereSuitePanelProvider;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\Widgets\AccountWidget;
use Modules\HumanResources\Filament\Pages\Auth\EditProfile;
use Modules\HumanResources\Filament\Pages\Auth\Login;
use Modules\HumanResources\Filament\Pages\Auth\Register;
use Modules\HumanResources\Filament\Pages\Auth\RequestPasswordReset;

/**
 * Panel della piattaforma HR (/hr): la porta d'ingresso della suite.
 *
 * È l'unico panel con le pagine di autenticazione (login, reset password, verifica email,
 * 2FA): gli altri panel rimandano qui chi non è loggato.
 */
class HumanResourcesPanelProvider extends VivereSuitePanelProvider
{
    protected function configureModule(Panel $panel): Panel
    {
        $panel = $panel
            ->id('hr')
            ->path('hr')
            ->default()
            ->brandName('Vivere HR')

            // ----- Autenticazione (pagine in Modules/HumanResources/app/Filament/Pages/Auth) -----
            ->login(Login::class)                       // con username o email
            ->registration(Register::class)             // form completo del documento, account "in attesa"
            ->passwordReset(RequestPasswordReset::class) // con username o email, mail oscurata nella conferma
            ->emailVerification()
            ->emailChangeVerification()                 // il cambio di mail va confermato dal nuovo indirizzo
            ->profile(EditProfile::class, isSimple: false)
            // 2FA via email obbligatoria per tutti (User::hasEmailAuthentication() è sempre true)
            ->multiFactorAuthentication([EmailAuthentication::make()], isRequired: true)

            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
            ]);

        return $this->discoverModuleComponents($panel, 'HumanResources');
    }
}
