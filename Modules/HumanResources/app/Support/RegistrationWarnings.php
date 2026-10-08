<?php

namespace Modules\HumanResources\Support;

use App\Models\Ban;
use App\Models\Professor;
use App\Models\User;

/**
 * Avvisi mostrati allo staff quando rivede un utente (HR 2.2.1-2.2.2, D2, D12).
 * Sono solo avvisi: decidere spetta sempre allo staffer, non c'è mai un rifiuto automatico.
 */
final class RegistrationWarnings
{
    /**
     * @return list<string>
     */
    public static function for(User $user): array
    {
        $warnings = [];

        if (self::matchesBannedUser($user)) {
            $warnings[] = 'Somiglia a un utente bannato (stessa mail istituzionale salvo i numeri finali).';
        }

        if (self::matchesProfessor($user)) {
            $warnings[] = 'Nome e cognome coincidono con quelli di un professore dell\'ateneo.';
        }

        if (UnipaIdentity::isInstitutionalEmail($user->email)
            && ! UnipaIdentity::emailMatchesName($user->email, $user->name, $user->surname)) {
            $warnings[] = 'Nome e cognome non corrispondono alla mail istituzionale.';
        }

        return $warnings;
    }

    public static function matchesBannedUser(User $user): bool
    {
        return Ban::query()
            ->where('email_pattern', UnipaIdentity::emailPattern($user->email))
            ->where('user_id', '!=', $user->id)
            ->exists();
    }

    /**
     * Confronto sul nome normalizzato, in entrambi gli ordini (i siti di ateneo spesso
     * scrivono prima il cognome).
     */
    public static function matchesProfessor(User $user): bool
    {
        $name = UnipaIdentity::normalize($user->name);
        $surname = UnipaIdentity::normalize($user->surname);

        return Professor::query()->whereIn('normalized_name', [$name.$surname, $surname.$name])->exists();
    }
}
