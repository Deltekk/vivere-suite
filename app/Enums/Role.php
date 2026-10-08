<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Ruolo di una persona all'interno della suite (colonna core.users.role).
 *
 * I ruoli sono gerarchici: ogni ruolo ha tutti i poteri di quelli che lo precedono.
 * Per questo non usiamo spatie/laravel-permission: bastano questo enum e le Policy.
 * I poteri "di contesto" (admin di un corso, gestore di un'auletta) sono relazioni
 * dedicate (core.admin_courses, core.auletta_managers), non ruoli.
 */
enum Role: string implements HasLabel
{
    /** Utente semplice, senza poteri particolari. */
    case Student = 'Student';

    /** Associato: può accettare le registrazioni e ha poteri di gestione su alcune piattaforme. */
    case Staff = 'Staff';

    /** Configura le piattaforme, gestisce i profili, può bannare. */
    case Admin = 'Admin';

    /** Come l'admin, ma non può essere revocato ed è l'unico che legge i log di audit. */
    case SuperAdmin = 'SuperAdmin';

    /**
     * Livello gerarchico del ruolo: più è alto, più poteri ha.
     */
    public function level(): int
    {
        return match ($this) {
            self::Student => 0,
            self::Staff => 1,
            self::Admin => 2,
            self::SuperAdmin => 3,
        };
    }

    /**
     * Vero se questo ruolo ha almeno i poteri di $role (es. Admin->isAtLeast(Staff) === true).
     */
    public function isAtLeast(self $role): bool
    {
        return $this->level() >= $role->level();
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Student => 'Studente',
            self::Staff => 'Staff',
            self::Admin => 'Amministratore',
            self::SuperAdmin => 'Super admin',
        };
    }
}
