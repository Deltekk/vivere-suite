<?php

namespace Modules\Kaffettino\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Motivo di un movimento di magazzino (kaffettino.stock_movements.reason).
 * Le uscite per vendita non sono qui: stanno nelle righe dei movimenti (transaction_items).
 */
enum StockReason: string implements HasLabel
{
    case Restock = 'Restock';
    case Loss = 'Loss';
    case Correction = 'Correction';

    public function getLabel(): string
    {
        return match ($this) {
            self::Restock => 'Carico merce',
            self::Loss => 'Perdita (scaduto, rotto, smarrito)',
            self::Correction => 'Correzione inventario',
        };
    }
}
