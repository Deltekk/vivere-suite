<?php

namespace Modules\HumanResources\Filament\Resources\Classrooms\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Modules\HumanResources\Filament\Resources\Classrooms\ClassroomResource;

class ManageClassrooms extends ManageRecords
{
    protected static string $resource = ClassroomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
