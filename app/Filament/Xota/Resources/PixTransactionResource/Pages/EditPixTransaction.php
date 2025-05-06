<?php

namespace App\Filament\Xota\Resources\PixTransactionResource\Pages;

use App\Filament\Xota\Resources\PixTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPixTransaction extends EditRecord
{
    protected static string $resource = PixTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
