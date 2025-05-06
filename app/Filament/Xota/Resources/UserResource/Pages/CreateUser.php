<?php

namespace App\Filament\Xota\Resources\UserResource\Pages;

use App\Filament\Xota\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
