<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Genere dichiarato (colonna core.users.gender, facoltativa).
 *
 * Serve solo a declinare i testi della piattaforma (es. "Benvenuto/Benvenuta"):
 * se non è indicato si usa la forma neutra. Vedi User::inflect().
 */
enum Gender: string implements HasLabel
{
    case Male = 'Male';
    case Female = 'Female';
    case Other = 'Other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Male => 'Maschile',
            self::Female => 'Femminile',
            self::Other => 'Altro',
        };
    }
}
