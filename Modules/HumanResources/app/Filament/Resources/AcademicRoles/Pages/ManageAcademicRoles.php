<?php

namespace Modules\HumanResources\Filament\Resources\AcademicRoles\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Modules\HumanResources\Filament\Resources\AcademicRoles\AcademicRoleResource;

class ManageAcademicRoles extends ManageRecords
{
    protected static string $resource = AcademicRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
