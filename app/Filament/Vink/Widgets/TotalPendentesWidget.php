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

        /**
         * 🔸 Pendentes Pluggou (status = 'pending' no banco)
         */
        $pendentesHoje = PluggouWebhook::where('status', 'pending')
            ->whereDate('created_at', $hoje)
            ->get();

        $qtdPendentes = $pendentesHoje->count();

        $valorPendentes = $pendentesHoje->sum(function ($r) {
            $data = $r->payload['data'] ?? [];
            return $data['amount'] ?? 0;
        });

        /**
         * ✅ Pix pagos hoje (status = 'paid')
         */
        $pagosHoje = PixTransaction::where('status', 'paid')
            ->whereDate('created_at', $hoje)
            ->get();

        $qtdPagos = $pagosHoje->count();
        $valorPagos = $pagosHoje->sum('amount'); // valor em centavos

        return [
            Card::make('Pendentes Pluggou', 'R$ ' . number_format($valorPendentes, 2, ',', '.'))
                ->description("{$qtdPendentes} pendentes hoje")
                ->color('warning'),

            Card::make('Pagos Hoje', 'R$ ' . number_format($valorPagos / 100, 2, ',', '.'))
                ->description("{$qtdPagos} pagos hoje")
                ->color('success'),
        ];
    }
}
