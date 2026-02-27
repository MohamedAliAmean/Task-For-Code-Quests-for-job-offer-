<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Enums\CourseStatus;
use App\Filament\Resources\CourseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['status'] ?? null) === CourseStatus::Published->value) {
            $data['published_at'] ??= now();
        } else {
            $data['published_at'] = null;
        }

        return $data;
    }
}
