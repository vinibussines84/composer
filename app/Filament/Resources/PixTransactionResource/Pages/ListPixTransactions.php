<?php

namespace App\Filament\Resources\PixTransactionResource\Pages;

use App\Filament\Resources\PixTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListPixTransactions extends ListRecords
{
    protected static string $resource = PixTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return []; // <- Remove o botão “+ Nova transação”
    }
}
