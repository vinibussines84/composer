<?php

namespace App\Filament\Xota\Widgets;

use App\Models\WithdrawRequest;
use App\Models\PixTransaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\HtmlString;

class WithdrawRequestStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        $hoje = Carbon::now('America/Sao_Paulo')->startOfDay();
        $inicioSemana = $hoje->copy()->startOfWeek();
        $fimSemana = $hoje->copy()->endOfWeek();
        $mesAtual = $hoje->translatedFormat('F Y');

        // PIX HOJE = entradas pagas + saques autorizados
        $pixHojeIn = PixTransaction::whereDate('created_at', $hoje)
            ->where('status', 'paid')
            ->where('balance_type', 1)
            ->sum('amount');

        $pixHojeOut = WithdrawRequest::whereDate('created_at', $hoje)
            ->where('status', 'autorizado')
            ->sum('amount');

        $pixHoje = ($pixHojeIn + $pixHojeOut) / 100;

        // PIX SEMANA
        $pixSemanaIn = PixTransaction::whereBetween('created_at', [$inicioSemana, $fimSemana])
            ->where('status', 'paid')
            ->where('balance_type', 1)
            ->sum('amount');

        $pixSemanaOut = WithdrawRequest::whereBetween('created_at', [$inicioSemana, $fimSemana])
            ->where('status', 'autorizado')
            ->sum('amount');

        $pixSemana = ($pixSemanaIn + $pixSemanaOut) / 100;

        // PIX MÊS
        $pixMesIn = PixTransaction::whereMonth('created_at', $hoje->month)
            ->whereYear('created_at', $hoje->year)
            ->where('status', 'paid')
            ->where('balance_type', 1)
            ->sum('amount');

        $pixMesOut = WithdrawRequest::whereMonth('created_at', $hoje->month)
            ->whereYear('created_at', $hoje->year)
            ->where('status', 'autorizado')
            ->sum('amount');

        $pixMes = ($pixMesIn + $pixMesOut) / 100;

        // Cash OUT do dia
        $cashOutSumHoje = $pixHojeOut / 100;
        $cashOutCountHoje = WithdrawRequest::whereDate('created_at', $hoje)
            ->where('status', 'autorizado')
            ->count();

        // Cash IN do dia
        $cashInCollection = PixTransaction::whereDate('created_at', $hoje)
            ->where('status', 'paid')
            ->where('balance_type', 1)
            ->get();
        $cashInTotal = $cashInCollection->sum('amount') / 100;
        $cashInCount = $cashInCollection->count();

        // Comissão dia e semana
        $comissaoDia = 0;
        foreach ($cashInCollection as $tx) {
            $comissaoDia += ($tx->amount / 100) * ($tx->user?->taxa_cash_in ?? 0) / 100;
        }
        foreach (WithdrawRequest::whereDate('created_at', $hoje)
                     ->where('status', 'autorizado')
                     ->get() as $saque) {
            $comissaoDia += ($saque->amount / 100) * ($saque->user?->taxa_cash_out ?? 0) / 100;
        }

        $comissaoSemana = 0;
        foreach (PixTransaction::whereBetween('created_at', [$inicioSemana, $fimSemana])
                     ->where('status', 'paid')
                     ->where('balance_type', 1)
                     ->get() as $tx) {
            $comissaoSemana += ($tx->amount / 100) * ($tx->user?->taxa_cash_in ?? 0) / 100;
        }
        foreach (WithdrawRequest::whereBetween('created_at', [$inicioSemana, $fimSemana])
                     ->where('status', 'autorizado')
                     ->get() as $saque) {
            $comissaoSemana += ($saque->amount / 100) * ($saque->user?->taxa_cash_out ?? 0) / 100;
        }

        // Gráfico IN vs OUT
        $chartData = [
            $cashInTotal,
            -$cashOutSumHoje,
        ];

        // Cor do card baseada no saldo líquido
        $netTotal = $cashInTotal - $cashOutSumHoje;
        $cardColor = $netTotal >= 0 ? 'success' : 'danger';

        $cards = [];

        $cards[] = Card::make('PIX HOJE', 'R$ ' . number_format($pixHoje, 2, ',', '.'))
            ->description($hoje->format('d/m/Y'))
            ->icon('heroicon-o-calendar')
            ->descriptionIcon('heroicon-o-check-circle')
            ->color('warning');

        $cards[] = Card::make('PIX SEMANA', 'R$ ' . number_format($pixSemana, 2, ',', '.'))
            ->description($inicioSemana->format('d/m') . ' a ' . $fimSemana->format('d/m'))
            ->icon('heroicon-o-calendar')
            ->color('primary');

        $cards[] = Card::make('PIX MÊS', 'R$ ' . number_format($pixMes, 2, ',', '.'))
            ->description($mesAtual)
            ->icon('heroicon-o-calendar-days')
            ->color('success');

        $cards[] = Card::make('TRANSAÇÕES DE HOJE', '')
            ->chart(array_values($chartData))
            ->icon('heroicon-o-currency-dollar')
            ->color($cardColor)
            ->description(new HtmlString(
                "Cash IN: R$ " . number_format($cashInTotal, 2, ',', '.') . " ({$cashInCount})<br>" .
                "Cash OUT: R$ " . number_format($cashOutSumHoje, 2, ',', '.') . " ({$cashOutCountHoje})"
            ));

        $cards[] = Card::make('COMISSÃO BRUTA', 'R$ ' . number_format($comissaoDia, 2, ',', '.'))
            ->description(new HtmlString("Semana: R$ " . number_format($comissaoSemana, 2, ',', '.')))
            ->icon('heroicon-o-banknotes')
            ->color('danger');

        $cards[] = Card::make('MEDS HOJE', '0')
            ->description('Total valor: R$ 0')
            ->icon('heroicon-o-receipt-refund')
            ->color('gray');

        return $cards;
    }
}
