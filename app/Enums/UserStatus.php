<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Stato dell'account di una persona (colonna core.users.status).
 */
enum UserStatus: string implements HasLabel
{
    /**
     * Appena registrato, in attesa che uno staffer accetti la richiesta.
     * Ci finisce anche chi, in registrazione, risulta simile a un professore o a un utente
     * bannato: in quel caso agli staffer viene mostrato un warning, mai un rifiuto automatico.
     */
    case Pending = 'Pending';

    /** Account accettato da uno staffer. */
    case Active = 'Active';

    /** Bannato per comportamenti scorretti (motivazione e storico in core.bans). */
    case Banned = 'Banned';

    /**
     * A inizio anno accademico l'anno di corso viene incrementato in automatico:
     * gli account che richiedono una verifica manuale (es. studenti delle superiori)
     * finiscono qui finché un admin non conferma il dato.
     */
    case ToConfirm = 'To_Confirm';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'In attesa',
            self::Active => 'Attivo',
            self::Banned => 'Bannato',
            self::ToConfirm => 'Da confermare',
        };
    }
}
