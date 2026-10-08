<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Tipo di dispositivo embedded (colonna core.devices.type).
 * Ogni modulo con un dispositivo aggiunge qui il suo tipo (e il valore nella migration).
 */
enum DeviceType: string implements HasLabel
{
    /** ESP32 con lettore NFC, tastierino e display nelle aulette (Kaffettino). */
    case KaffettinoReader = 'KaffettinoReader';

    public function getLabel(): string
    {
        return match ($this) {
            self::KaffettinoReader => 'Lettore Kaffettino',
        };
    }
}
