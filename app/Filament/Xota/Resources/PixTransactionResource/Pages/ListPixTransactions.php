<?php

namespace App\Filament\Xota\Resources\PixTransactionResource\Pages;

use App\Filament\Xota\Resources\PixTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPixTransactions extends ListRecords
{
    protected static string $resource = PixTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
