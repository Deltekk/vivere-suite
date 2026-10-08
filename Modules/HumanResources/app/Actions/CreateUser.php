<?php

namespace Modules\HumanResources\Actions;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Modules\HumanResources\Notifications\WelcomeSetPassword;

/**
 * Un admin inserisce un utente a mano. L'account è subito attivo; l'utente riceve una mail
 * con il link per scegliere la password (nessuno, nemmeno l'admin, conosce la password).
 * Cliccare quel link dimostra anche che la mail è sua (vedi MarkEmailVerifiedOnPasswordReset).
 */
class CreateUser
{
    /**
     * @param  array<string, mixed>  $data  Campi fillable di core.users (senza password)
     */
    public function handle(array $data, User $createdBy): User
    {
        Gate::forUser($createdBy)->authorize('create', User::class);

        $user = new User([
            ...$data,
            'password' => Str::password(32), // Provvisoria e sconosciuta: verrà scelta dall'utente
            'privacy_accepted_at' => now(),  // TODO: l'utente creato dallo staff deve accettare l'informativa al primo accesso
            'terms_version' => config('vivere.terms_version'),
        ]);
        $user->status = UserStatus::Active;
        $user->course_year_confirmed_at = now();
        $user->save();

        $user->notify(new WelcomeSetPassword(Password::broker()->createToken($user)));

        return $user;
    }
}
