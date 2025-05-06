<?php

namespace App\Filament\Xota\Resources\BalancesResource\Pages;

use App\Filament\Xota\Resources\BalancesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBalances extends EditRecord
{
    protected static string $resource = BalancesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
