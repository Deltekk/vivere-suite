<?php

namespace Modules\HumanResources\Filament\Resources\Courses\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\HumanResources\Filament\Resources\Courses\CourseResource;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;
}
