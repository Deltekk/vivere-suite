<?php

namespace Modules\HumanResources\Filament\Resources\Macroareas\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Modules\HumanResources\Filament\Resources\Macroareas\MacroareaResource;

class ManageMacroareas extends ManageRecords
{
    protected static string $resource = MacroareaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
