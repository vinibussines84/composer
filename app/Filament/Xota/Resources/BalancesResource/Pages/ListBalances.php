<?php

namespace App\Filament\Xota\Resources\BalancesResource\Pages;

use App\Filament\Xota\Resources\BalancesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBalances extends ListRecords
{
    protected static string $resource = BalancesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
