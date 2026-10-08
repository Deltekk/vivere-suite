<?php

namespace Modules\HumanResources\Filament\Resources\Aulette\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\HumanResources\Filament\Resources\Aulette\AulettaResource;

class ListAulette extends ListRecords
{
    protected static string $resource = AulettaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
