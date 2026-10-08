<?php

namespace Modules\HumanResources\Filament\Resources\Courses\Pages;

use App\Filament\Actions\DeleteIfUnusedAction;
use Filament\Resources\Pages\EditRecord;
use Modules\HumanResources\Filament\Resources\Courses\CourseResource;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteIfUnusedAction::make(),
        ];
    }
}
