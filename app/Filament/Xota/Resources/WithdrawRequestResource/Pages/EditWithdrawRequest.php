<?php

namespace App\Filament\Xota\Resources\WithdrawRequestResource\Pages;

use App\Filament\Xota\Resources\WithdrawRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWithdrawRequest extends EditRecord
{
    protected static string $resource = WithdrawRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
