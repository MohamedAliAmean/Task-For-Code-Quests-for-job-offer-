<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $role = $data['role'] ?? null;
        $roleValue = $role instanceof UserRole ? $role->value : (string) $role;

        $data['is_admin'] = $roleValue === UserRole::Admin->value;

        return $data;
    }
}
