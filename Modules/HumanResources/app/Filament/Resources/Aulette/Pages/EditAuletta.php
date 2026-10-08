<?php

namespace Modules\HumanResources\Filament\Resources\Aulette\Pages;

use App\Filament\Actions\DeleteIfUnusedAction;
use Filament\Resources\Pages\EditRecord;
use Modules\HumanResources\Filament\Resources\Aulette\AulettaResource;

class EditAuletta extends EditRecord
{
    protected static string $resource = AulettaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteIfUnusedAction::make(),
        ];
    }
}
