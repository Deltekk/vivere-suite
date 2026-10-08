<?php

namespace App\Support;

/**
 * Informazioni sulla suite nel suo insieme, usate da più moduli.
 */
final class Suite
{
    /**
     * Collegamento tra id del panel Filament e alias del modulo (core.changelogs.service,
     * core.email_preferences.service). Va aggiornato quando un modulo ottiene il suo panel.
     */
    private const PANEL_SERVICES = [
        'hr' => 'humanresources',
        'kaffettino' => 'kaffettino',
    ];

    public static function serviceForPanel(string $panelId): string
    {
        return self::PANEL_SERVICES[$panelId] ?? $panelId;
    }

    /**
     * Servizi che hanno un panel, con il nome da mostrare (per i changelog).
     *
     * @return array<string, string>
     */
    public static function panelServices(): array
    {
        return [
            'humanresources' => 'Risorse Umane',
            'kaffettino' => 'Kaffettino',
        ];
    }
}
