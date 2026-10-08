<?php

namespace Modules\HumanResources\Filament\Resources\Schools\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Modules\HumanResources\Filament\Resources\Schools\SchoolResource;

class ManageSchools extends ManageRecords
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
