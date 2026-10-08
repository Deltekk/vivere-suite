<?php

namespace Modules\HumanResources\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Password;
use Modules\HumanResources\Support\UnipaIdentity;

/**
 * Richiesta di reset della password con username o email (HR 2.2.2): il link arriva alla mail
 * dell'account e la conferma mostra l'indirizzo oscurato (es. ma***03@community.unipa.it),
 * così l'utente sa dove guardare senza che la mail venga mostrata per intero.
 */
class RequestPasswordReset extends BaseRequestPasswordReset
{
    /** Mail oscurata dell'account trovato, mostrata nella conferma. */
    protected ?string $maskedEmail = null;

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
    protected function getCredentialsFromFormData(array $data): array
    {
        $credentials = Login::identifierCredentials((string) $data['email']);

        $user = User::query()->where(function ($query) use ($credentials): void {
            foreach ($credentials as $column => $value) {
                is_callable($value) ? $value($query) : $query->where($column, $value);
            }
        })->first();
        $this->maskedEmail = $user ? UnipaIdentity::maskEmail($user->email) : null;

        return $credentials;
    }

    protected function getSentNotification(string $status): ?Notification
    {
        return Notification::make()
            ->title('Controlla la tua mail')
            ->body($status === Password::RESET_LINK_SENT && $this->maskedEmail
                ? "Ti abbiamo mandato il link per reimpostare la password a {$this->maskedEmail}."
                : null)
            ->success();
    }
}
