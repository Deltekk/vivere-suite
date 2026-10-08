<?php

namespace Modules\HumanResources\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use SensitiveParameter;

/**
 * Login con username (formato UNIPA, es. MarioLuigi.Rossi03) oppure con email (HR 2.2.2).
 *
 * Il campo resta "email" nel form solo per riusare la logica di Filament (rate limiting,
 * messaggi di errore); il valore può essere uno username o una mail.
 */
class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Username o email')
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [
            ...self::identifierCredentials((string) $data['email']),
            'password' => $data['password'],
        ];
    }

    /**
     * Credenziali per trovare l'utente: le mail si confrontano in minuscolo, gli username senza
     * distinguere le maiuscole (indice lower(username)). Laravel accetta una closure come valore.
     *
     * @return array<string, mixed>
     */
    public static function identifierCredentials(string $identifier): array
    {
        $identifier = Str::lower(trim($identifier));

        return str_contains($identifier, '@')
            ? ['email' => $identifier]
            : ['username' => fn (Builder $query) => $query->whereRaw('lower(username) = ?', [$identifier])];
    }
}
