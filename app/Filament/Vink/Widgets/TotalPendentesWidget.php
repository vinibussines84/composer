<?php

namespace App\Filament\Vink\Widgets;

use App\Models\BloobankWebhook;
use App\Models\PixTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class TotalPendentesWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $hoje = Carbon::today();

        // 🔸 Pendentes da Pluggou Hoje
        $webhooksHoje = BloobankWebhook::whereDate('created_at', $hoje)->get();

        $pendentesHoje = $webhooksHoje->filter(function ($record) {
            $data = json_decode($record->payload, true)['data'] ?? [];
            return ($data['status'] ?? null) === 'pending';
        });

        $valorPendentes = $pendentesHoje->sum(function ($record) {
            $data = json_decode($record->payload, true)['data'] ?? [];
            // aqui 'amount' já vem em centavos
            return $data['amount'] ?? 0;
        });

        $qtdPendentes = $pendentesHoje->count();

        // ✅ Pagos Hoje (PixTransaction status = paid)
        $pagasHoje = PixTransaction::where('status', 'paid')
            ->whereDate('created_at', $hoje)
            ->get();

        $valorPagos = $pagasHoje->sum('amount');
        $qtdPagos   = $pagasHoje->count();

        return [
            Card::make('Pendentes Pluggou', 'R$ ' . number_format($valorPendentes / 100, 2, ',', '.'))
                ->description($qtdPendentes . ' pendentes hoje')
                ->color('warning'),

            Card::make('Pagos Hoje', 'R$ ' . number_format($valorPagos / 100, 2, ',', '.'))
                ->description($qtdPagos . ' pagos hoje')
                ->color('success'),
        ];
    }
}
