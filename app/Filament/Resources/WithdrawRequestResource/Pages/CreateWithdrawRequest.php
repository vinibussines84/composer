<?php

namespace App\Filament\Resources\WithdrawRequestResource\Pages;

use App\Filament\Resources\WithdrawRequestResource;
use App\Models\WithdrawRequest;
use Filament\Resources\Pages\CreateRecord;

class CreateWithdrawRequest extends CreateRecord
{
    protected static string $resource = WithdrawRequestResource::class;

    protected function handleRecordCreation(array $data): WithdrawRequest
    {
        $user = auth()->user();
        $taxa = $user->taxa_cash_out ?? 0;

        $valorSolicitado = $data['amount'];
        $valorLiquido = $valorSolicitado - ($valorSolicitado * ($taxa / 100));

        $data['amount'] = intval($valorLiquido * 100); // Salva em centavos
        $data['user_id'] = $user->id;

        return WithdrawRequest::create($data);
    }
}
