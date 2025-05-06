<?php

namespace App\Filament\Xota\Widgets;

use App\Models\WithdrawRequest;
use App\Models\PixTransaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\DB;

class WithdrawRequestStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        $hoje = Carbon::now('America/Sao_Paulo')->startOfDay();
        $inicioSemana = $hoje->copy()->startOfWeek();
        $fimSemana = $hoje->copy()->endOfWeek();
        $mesAtual = $hoje->translatedFormat('F Y');

        // PIX (saques autorizados)
        $pixHoje = WithdrawRequest::whereDate('created_at', $hoje)->where('status', 'autorizado')->count();
        $pixSemana = WithdrawRequest::whereBetween('created_at', [$inicioSemana, $fimSemana])->where('status', 'autorizado')->count();
        $pixMes = WithdrawRequest::whereMonth('created_at', $hoje->month)->whereYear('created_at', $hoje->year)->where('status', 'autorizado')->count();

        // Cash OUT do dia
        $cashOutHoje = WithdrawRequest::whereDate('created_at', $hoje)
            ->where('status', 'autorizado')
            ->sum('amount') / 100;

        $cashOutCountHoje = WithdrawRequest::whereDate('created_at', $hoje)
            ->where('status', 'autorizado')
            ->count();

        // Comissão do dia
        $comissaoDia = 0;
        $comissaoSemana = 0;

        // Cash IN do dia
        $cashInsHoje = PixTransaction::whereDate('created_at', $hoje)
            ->where('status', 'paid')
            ->get();

        foreach ($cashInsHoje as $transacao) {
            $taxa = $transacao->user?->taxa_cash_in ?? 0;
            $valor = $transacao->amount / 100;
            $comissaoDia += $valor * ($taxa / 100);
        }

        // Cash OUT do dia
        $cashOutsHoje = WithdrawRequest::whereDate('created_at', $hoje)
            ->where('status', 'autorizado')
            ->get();

        foreach ($cashOutsHoje as $saque) {
            $taxa = $saque->user?->taxa_cash_out ?? 0;
            $valor = $saque->amount / 100;
            $comissaoDia += $valor * ($taxa / 100);
        }

        // Semana
        $cashInsSemana = PixTransaction::whereBetween('created_at', [$inicioSemana, $fimSemana])
            ->where('status', 'paid')
            ->get();

        foreach ($cashInsSemana as $transacao) {
            $taxa = $transacao->user?->taxa_cash_in ?? 0;
            $valor = $transacao->amount / 100;
            $comissaoSemana += $valor * ($taxa / 100);
        }

        $cashOutsSemana = WithdrawRequest::whereBetween('created_at', [$inicioSemana, $fimSemana])
            ->where('status', 'autorizado')
            ->get();

        foreach ($cashOutsSemana as $saque) {
            $taxa = $saque->user?->taxa_cash_out ?? 0;
            $valor = $saque->amount / 100;
            $comissaoSemana += $valor * ($taxa / 100);
        }

        // Gráfico de Cash IN por hora
        $hours = range(0, 23);
        $cashInPerHour = PixTransaction::selectRaw("strftime('%H', created_at) as hour, count(*) as total")
            ->whereDate('created_at', $hoje)
            ->where('balance_type', 1)
            ->where('status', 'paid')
            ->groupBy(DB::raw("strftime('%H', created_at)"))
            ->pluck('total', 'hour')
            ->toArray();

        $chartData = [];
        foreach ($hours as $hour) {
            $key = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $chartData[] = $cashInPerHour[$key] ?? 0;
        }

        return [
            Card::make('PIX HOJE', (string) $pixHoje)
                ->description($hoje->format('d/m/Y'))
                ->icon('heroicon-o-calendar')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('warning'),

            Card::make('PIX SEMANA', (string) $pixSemana)
                ->description($inicioSemana->format('d/m') . ' a ' . $fimSemana->format('d/m'))
                ->icon('heroicon-o-calendar')
                ->color('primary'),

            Card::make('PIX MÊS', (string) $pixMes)
                ->description($mesAtual)
                ->icon('heroicon-o-calendar-days')
                ->color('success'),

            Card::make('TRANSAÇÕES DE HOJE', '')
                ->icon('heroicon-o-currency-dollar')
                ->chart($chartData)
                ->description(new HtmlString(
                    "Cash IN: R$ 0,00 (0)<br>Cash OUT: R$ " . number_format(-$cashOutHoje, 2, ',', '.') . " ({$cashOutCountHoje})"
                ))
                ->color('danger'),

            Card::make('COMISSÃO BRUTA', 'R$ ' . number_format($comissaoDia, 2, ',', '.'))
                ->description(new HtmlString("Semana: R$ " . number_format($comissaoSemana, 2, ',', '.')))
                ->icon('heroicon-o-banknotes')
                ->color('danger'),

            Card::make('MEDS HOJE', '0')
                ->description('Total valor: R$ 0')
                ->icon('heroicon-o-receipt-refund')
                ->color('gray'),
        ];
    }
}
