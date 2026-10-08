<?php

namespace Modules\Kaffettino\Providers\Filament;

use App\Providers\Filament\VivereSuitePanelProvider;
use Filament\Pages\Dashboard;
use Filament\Panel;

/**
 * Panel della piattaforma Kaffettino (/kaffettino).
 *
 * Non ha pagine di login: l'autenticazione è gestita da HR (vedi VivereSuitePanelProvider).
 * Chi può entrare è deciso da User::canAccessPanel() (solo staff attivo).
 */
class KaffettinoPanelProvider extends VivereSuitePanelProvider
{
    protected function configureModule(Panel $panel): Panel
    {
        $panel = $panel
            ->id('kaffettino')
            ->path('kaffettino')
            ->brandName('Vivere Kaffettino')
            // TODO: home con l'immagine "buongiornissimo kaffè!" al posto della dashboard standard
            ->pages([
                Dashboard::class,
            ]);

        return $this->discoverModuleComponents($panel, 'Kaffettino');
    }
}
