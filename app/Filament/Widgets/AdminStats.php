<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\User;

class AdminStats extends BaseWidget
{
    protected function getCards(): array
    {
        $user = auth()->user();
        $saldo = ($user->saldo ?? 0) / 100;
        $bloqueado = ($user->bloqueado ?? 0) / 100;
        $disponivel = $saldo - $bloqueado;

        $disponivelDescricao = $disponivel > 0
            ? 'Disponível para Saque.'
            : 'Conta com Meds Pendentes';

        $disponivelCor = $disponivel > 0
            ? 'success'
            : 'danger';

        return [
            Card::make('Saldo Disponível', 'R$ ' . number_format($disponivel, 2, ',', '.'))
                ->description($disponivelDescricao)
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color($disponivelCor),

            Card::make('Valor Bloqueado', 'R$ ' . number_format($bloqueado, 2, ',', '.'))
                ->description('Em contestação ou estorno.')
                ->descriptionIcon('heroicon-o-lock-closed')
                ->color('warning'),
        ];
    }
}
