<?php

namespace App\Filament\Widgets;

use App\Models\PixTransaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;

class AdminStats extends BaseWidget
{
    protected function getCards(): array
    {
        $userId = Auth::id();
        $user = User::find($userId);

        if (! $user) {
            return [
                Card::make('Erro', 'Usuário não encontrado')->color('danger'),
            ];
        }

        // saldo e bloqueado estão armazenados em centavos (inteiros)
        $saldoCentavos = (int) $user->saldo;
        $bloqueadoCentavos = (int) $user->bloqueado;
        $disponivelCentavos = $saldoCentavos - $bloqueadoCentavos;

        $saldo = $saldoCentavos / 100;
        $bloqueado = $bloqueadoCentavos / 100;
        $disponivel = $disponivelCentavos / 100;

        $disponivelDescricao = $disponivel > 0
            ? 'Disponível para saque.'
            : 'Conta ativa.';
        $disponivelCor = $disponivel > 0 ? 'success' : 'danger';

        // Transações com status "paid" (Cash IN)
        $cashInHoje = PixTransaction::where('authkey', $user->authkey)
            ->where('gtkey', $user->gtkey)
            ->where('status', 'paid')
            ->whereDate('created_at', now())
            ->get();

        // Transações de saída (Cash OUT)
        $cashOutHoje = PixTransaction::where('authkey', $user->authkey)
            ->where('gtkey', $user->gtkey)
            ->where('balance_type', 0)
            ->whereDate('created_at', now())
            ->get();

        $cashInSumHoje = $cashInHoje->sum('amount') / 100;
        $cashInCountHoje = $cashInHoje->count();

        $cashOutSumHoje = $cashOutHoje->sum('amount') / 100;
        $cashOutCountHoje = $cashOutHoje->count();

        // Soma de taxas das transações "paid"
        $paidTransactions = PixTransaction::where('authkey', $user->authkey)
            ->where('gtkey', $user->gtkey)
            ->where('status', 'paid')
            ->get();

        $totalTaxas = $paidTransactions->sum(function ($tx) use ($user) {
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
                ->description('Taxas cobradas em transações pagas.')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('gray'),

            Card::make('📊 TRANSAÇÕES DE HOJE', '')
                ->icon('heroicon-o-chart-bar')
                ->chart($chartData)
                ->chartColor('success')
                ->description(new HtmlString(
                    'Cash IN: R$ ' . number_format($cashInSumHoje, 2, ',', '.') . " ({$cashInCountHoje})<br>" .
                    'Cash OUT: R$ ' . number_format($cashOutSumHoje, 2, ',', '.') . " ({$cashOutCountHoje})"
                ))
                ->descriptionColor('success'),
        ];
    }
}
