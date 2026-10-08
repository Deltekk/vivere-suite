<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Stato delle chiavi di un'auletta per un gestore (colonna core.auletta_managers.keys_state).
 *
 * Ciclo di vita: nomina (HandingOver, parte la mail "vieni a prendere le chiavi")
 * -> HandedOver -> rimozione (Requested, parte la mail "restituisci le chiavi") -> Returned.
 */
enum KeyState: string implements HasLabel
{
    /** Gestore nominato, deve ancora ritirare le chiavi. */
    case HandingOver = 'Handing_Over';

    /** Chiavi consegnate al gestore. */
    case HandedOver = 'Handed_Over';

    /** Gestore rimosso, è stata chiesta la restituzione delle chiavi. */
    case Requested = 'Requested';

    /** Chiavi restituite. */
    case Returned = 'Returned';

    public function getLabel(): string
    {
        return match ($this) {
            self::HandingOver => 'Da consegnare',
            self::HandedOver => 'Consegnate',
            self::Requested => 'Restituzione richiesta',
            self::Returned => 'Restituite',
        };
    }
}
