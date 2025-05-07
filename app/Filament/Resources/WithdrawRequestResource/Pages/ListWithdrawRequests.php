<?php

namespace App\Filament\Resources\WithdrawRequestResource\Pages;

use App\Filament\Resources\WithdrawRequestResource;
use App\Models\WithdrawRequest;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListWithdrawRequests extends ListRecords
{
    protected static string $resource = WithdrawRequestResource::class;

    public function getTabs(): array
    {
        $userId = Auth::id();

        return [
            'Todos' => Tab::make('📋 Todos')
                ->badge(WithdrawRequest::where('user_id', $userId)->count()),

            'Efetivados' => Tab::make('✅ Efetivados')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('user_id', $userId)
                    ->where('status', 'autorizado'))
                ->badge(WithdrawRequest::where('user_id', $userId)->where('status', 'autorizado')->count()),

            'Pendente' => Tab::make('🕓 Pendente')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('user_id', $userId)
                    ->where('status', 'pending'))
                ->badge(WithdrawRequest::where('user_id', $userId)->where('status', 'pending')->count()),

            'Cancelados' => Tab::make('❌ Cancelados')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('user_id', $userId)
                    ->where('status', 'cancelado'))
                ->badge(WithdrawRequest::where('user_id', $userId)->where('status', 'cancelado')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
