<?php

namespace Modules\HumanResources\Filament\Resources\Users\Pages;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\HumanResources\Actions\CreateUser as CreateUserAction;
use Modules\HumanResources\Filament\Resources\Users\UserResource;

/**
 * Inserimento manuale di un utente da parte di un admin: l'account è subito attivo e
 * l'utente riceve la mail per scegliere la password (Modules\HumanResources\Actions\CreateUser).
 */
class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var User $admin */
        $admin = Filament::auth()->user();

        return app(CreateUserAction::class)->handle($data, $admin);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Utente inserito: gli abbiamo mandato la mail per scegliere la password';
    }

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
