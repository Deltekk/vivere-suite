<?php

namespace Modules\Kaffettino\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Tipo di movimento (kaffettino.transactions.type). Il segno dell'importo è vincolato dal DB.
 */
enum TransactionType: string implements HasLabel
{
    /** Acquisto di uno o più prodotti: importo negativo. */
    case Purchase = 'Purchase';

    /** Ricarica fatta da un admin, mai sul proprio conto: importo positivo. */
    case TopUp = 'TopUp';

    /** Prodotti offerti dagli admin (es. a ospiti, sul conto dell'auletta): importo = -valore dei prodotti. */
    case Gift = 'Gift';

    /** Caffè gratis del compleanno: importo zero. */
    case BirthdayGift = 'BirthdayGift';

    /** Rettifica manuale di un admin, sempre con nota: importo con qualsiasi segno. */
    case Adjustment = 'Adjustment';

    public function getLabel(): string
    {
        return match ($this) {
            self::Purchase => 'Acquisto',
            self::TopUp => 'Ricarica',
            self::Gift => 'Omaggio',
            self::BirthdayGift => 'Regalo di compleanno',
            self::Adjustment => 'Rettifica',
        };
    }
}
