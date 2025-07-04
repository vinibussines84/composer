<?php

namespace App\Filament\Vink\Widgets;

use App\Models\PluggouWebhook;
use App\Models\PixTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class TotalPendentesWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $hoje = Carbon::today();

        //
        // 🔸 Pendentes Pluggou Hoje
        //
        $webhooksHoje = PluggouWebhook::whereDate('created_at', $hoje)->get();

        $pendentesHoje = $webhooksHoje->filter(function ($record) {
            $data = json_decode($record->payload, true)['data'] ?? [];
            return ($data['status'] ?? null) === 'pending';
        });

        // Supondo que em Pluggou o 'amount' já vem em reais (ex: 14844.56)
        $valorPendentes = $pendentesHoje->sum(function ($record) {
            $data = json_decode($record->payload, true)['data'] ?? [];
            return $data['amount'] ?? 0;
        });

        $qtdPendentes = $pendentesHoje->count();

        //
        // ✅ Pagos Hoje (PixTransaction status = paid)
        //
        $pagasHoje = PixTransaction::where('status', 'paid')
            ->whereDate('created_at', $hoje)
            ->get();

        $valorPagos = $pagasHoje->sum('amount');
        $qtdPagos   = $pagasHoje->count();

        return [
            Card::make('Pendentes Pluggou', 'R$ ' . number_format($valorPendentes, 2, ',', '.'))
                ->description($qtdPendentes . ' pendentes hoje')
                ->color('warning'),

            Card::make('Pagos Hoje', 'R$ ' . number_format($valorPagos / 100, 2, ',', '.'))
                ->description($qtdPagos . ' pagos hoje')
                ->color('success'),
        ];
    }
}
