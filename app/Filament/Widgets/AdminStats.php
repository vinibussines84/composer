<?php

namespace App\Filament\Widgets;

use App\Models\PixTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class AdminStats extends BaseWidget
{
    protected function getCards(): array
    {
        $user = Auth::user();

        $saldo      = ($user->saldo     ?? 0) / 100;
        $bloqueado  = ($user->bloqueado ?? 0) / 100;
        $disponivel = $saldo - $bloqueado;

        $disponivelDescricao = $disponivel > 0
            ? 'Disponível para saque.'
            : 'Conta ativa.';
        $disponivelCor = $disponivel > 0 ? 'success' : 'danger';

        // Busca transações do dia por authkey e gtkey
        $cashInHoje = PixTransaction::where('authkey', $user->authkey)
            ->where('gtkey', $user->gtkey)
            ->where('balance_type', 1)
            ->whereDate('created_at', now())
            ->get();

        $cashOutHoje = PixTransaction::where('authkey', $user->authkey)
            ->where('gtkey', $user->gtkey)
            ->where('balance_type', 0)
            ->whereDate('created_at', now())
            ->get();

        $cashInSumHoje   = $cashInHoje->sum('amount') / 100;
        $cashInCountHoje = $cashInHoje->count();

        $cashOutSumHoje   = $cashOutHoje->sum('amount') / 100;
        $cashOutCountHoje = $cashOutHoje->count();

        // Soma total de taxas baseado em todas as transações do usuário
        $totalTaxas = PixTransaction::where('authkey', $user->authkey)
            ->where('gtkey', $user->gtkey)
            ->get()
            ->sum(function ($tx) use ($user) {
                $taxa = $tx->balance_type == 1
                    ? $user->taxa_cash_in
                    : $user->taxa_cash_out;
                return ($tx->amount / 100) * ($taxa / 100);
            });

        $chartData = [
            $cashInSumHoje,
            $cashOutSumHoje,
        ];

        return [
            Card::make('💰 Saldo Disponível', 'R$ ' . number_format($disponivel, 2, ',', '.'))
                ->icon('heroicon-o-currency-dollar')
                ->description($disponivelDescricao)
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color($disponivelCor),

            Card::make('🔒 Valor Bloqueado', 'R$ ' . number_format($bloqueado, 2, ',', '.'))
                ->icon('heroicon-o-lock-closed')
                ->description('Em contestação ou estorno.')
                ->descriptionIcon('heroicon-o-lock-closed')
                ->color('warning'),

            Card::make('💸 Total de Taxas', 'R$ ' . number_format($totalTaxas, 2, ',', '.'))
                ->icon('heroicon-o-banknotes')
                ->description('Taxas cobradas em transações.')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('gray'),

            Card::make('📊 TRANSAÇÕES DE HOJE', '')
                ->icon('heroicon-o-chart-bar')
                ->chart($chartData)
                ->chartColor('success')
                ->description(new HtmlString(
                    "Cash IN: R$ " 
                        . number_format($cashInSumHoje, 2, ',', '.') 
                        . " ({$cashInCountHoje})<br>" .
                    "Cash OUT: R$ " 
                        . number_format($cashOutSumHoje, 2, ',', '.') 
                        . " ({$cashOutCountHoje})"
                ))
                ->descriptionColor('success'),
        ];
    }
}
