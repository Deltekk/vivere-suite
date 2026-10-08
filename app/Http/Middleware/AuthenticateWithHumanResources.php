<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;

/**
 * Middleware di autenticazione di tutti i panel della suite.
 *
 * Fa quello che fa il middleware standard di Filament (utente loggato + canAccessPanel), ma
 * chi non è loggato viene mandato sempre al login della piattaforma HR, l'unica con le pagine
 * di autenticazione (requisito del preambolo). Dopo il login Laravel lo riporta alla pagina
 * che aveva chiesto (redirect "intended").
 */
class AuthenticateWithHumanResources extends Authenticate
{
    protected function redirectTo($request): ?string
    {
        return Filament::getPanel('hr')->getLoginUrl();
    }
}
