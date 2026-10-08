<?php

namespace Modules\Kaffettino\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Da dove arriva un movimento (kaffettino.transactions.channel).
 */
enum TransactionChannel: string implements HasLabel
{
    /** Badge sull'ESP32 in auletta. */
    case Embedded = 'Embedded';

    /** Dal portale web (acquisto da web o ricarica). */
    case Web = 'Web';

    public function getLabel(): string
    {
        return match ($this) {
            self::Embedded => 'Dispositivo in auletta',
            self::Web => 'Portale web',
        };
    }
}
