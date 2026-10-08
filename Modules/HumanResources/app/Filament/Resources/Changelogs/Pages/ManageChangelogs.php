<?php

namespace Modules\HumanResources\Filament\Resources\Changelogs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Modules\HumanResources\Filament\Resources\Changelogs\ChangelogResource;

class ManageChangelogs extends ManageRecords
{
    protected static string $resource = ChangelogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
