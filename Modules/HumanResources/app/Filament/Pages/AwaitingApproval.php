<?php

namespace Modules\HumanResources\Filament\Pages;

use App\Enums\UserStatus;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Page;

/**
 * Schermata di attesa (HR 2.2.1-2.2.2): chi si è appena registrato la vede finché uno staffer
 * non accetta la richiesta, anche se prova a rientrare (vedi EnsureAccountIsReady).
 */
class AwaitingApproval extends Page
{
    protected static ?string $slug = 'in-attesa';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'humanresources::filament.pages.awaiting-approval';

    public function getTitle(): string
    {
        return 'Registrazione in attesa';
    }

    public function mount(): void
    {
        // Chi è già stato accettato non ha motivo di stare qui
        if ($this->user()->status !== UserStatus::Pending) {
            $this->redirect(Filament::getPanel('hr')->getUrl());
        }
    }

    public function user(): User
    {
        /** @var User */
        return Filament::auth()->user();
    }
}
