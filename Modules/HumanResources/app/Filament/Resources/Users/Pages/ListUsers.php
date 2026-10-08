<?php

namespace Modules\HumanResources\Filament\Resources\Users\Pages;

use App\Enums\UserStatus;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Modules\HumanResources\Filament\Resources\Users\UserResource;

/**
 * Elenco utenti diviso per stato: le registrazioni in attesa e gli anni da confermare
 * sono il lavoro quotidiano dello staff.
 */
class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Inserisci utente'),
        ];
    }

    public function getTabs(): array
    {
        $count = fn (UserStatus $status): int => User::query()->where('status', $status)->whereNull('anonymized_at')->count();

        return [
            'pending' => Tab::make('In attesa')
                ->badge($count(UserStatus::Pending) ?: null)->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::Pending)),
            'to_confirm' => Tab::make('Da confermare')
                ->badge($count(UserStatus::ToConfirm) ?: null)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::ToConfirm)),
            'active' => Tab::make('Attivi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::Active)->whereNull('anonymized_at')),
            'banned' => Tab::make('Bannati')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::Banned)),
            'all' => Tab::make('Tutti'),
        ];
    }

    /**
     * Si apre sulle registrazioni in attesa, se ce ne sono.
     */
    public function getDefaultActiveTab(): string|int|null
    {
        return User::query()->where('status', UserStatus::Pending)->exists() ? 'pending' : 'active';
    }
}
