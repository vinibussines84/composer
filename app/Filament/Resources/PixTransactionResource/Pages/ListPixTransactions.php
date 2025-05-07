<?php

namespace App\Filament\Resources\PixTransactionResource\Pages;

use App\Filament\Resources\PixTransactionResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPixTransactions extends ListRecords
{
    protected static string $resource = PixTransactionResource::class;

    public function getTabs(): array
    {
        return [
            'Todos' => ListRecords\Tab::make('📋 Todos'),

            'Pagos' => ListRecords\Tab::make('✅ Pagos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paid')),

            'Pendentes' => ListRecords\Tab::make('🕒 Pendentes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return []; // Remove o botão “+ Nova transação”
    }
}
