<?php

namespace Modules\HumanResources\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Disiscrizione dalla piattaforma (HR 2.2.2, GDPR): i dati personali vengono sostituiti con
 * valori anonimi invece di cancellare la riga, così lo storico (movimenti, mandati, log) resta
 * coerente ma non riconduce più alla persona.
 *
 * Restano solo i dati necessari per obblighi o interessi legittimi dell'associazione:
 * lo storico dei ban (con il pattern della mail, per riconoscere chi si registra di nuovo dopo
 * un ban) e i movimenti di Kaffettino.
 */
class AnonymizeUser
{
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $placeholder = 'anonimo-'.Str::lower(Str::random(12));

            $user->fill([
                'name' => 'Utente',
                'surname' => 'anonimo',
                'username' => $placeholder,
                'email' => "{$placeholder}@anonimo.invalid",
                'birthday' => '1900-01-01',
                'phone_number' => '-',
                'telegram_tag' => null,
                'instagram_tag' => null,
                'gender' => null,
                'school_id' => null,
                'password' => Str::password(32),
            ]);
            $user->remember_token = null;
            $user->anonymized_at = now();
            $user->save();

            $user->staffProfile()->delete();
            $user->emailPreferences()->delete();
            $user->notifications()->delete();
        });

        activity('humanresources')->performedOn($user)->event('anonymized')->log('Account anonimizzato su richiesta dell\'utente');
    }
}
