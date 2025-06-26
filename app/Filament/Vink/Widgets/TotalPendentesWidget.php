<?php

namespace App\Filament\Vink\Widgets;

use App\Models\BloobankWebhook;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class TotalPendentesWidget extends BaseWidget
{
    protected function getCards(): array
    {
        // 🔶 Total pendente HOJE
        $pendentesHoje = BloobankWebhook::where('status', 'pending')
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->get();

        $totalPendentesCentavos = $pendentesHoje->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        // ✅ Total aprovado HOJE
        $aprovadosHoje = BloobankWebhook::where('status', 'processed')
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->get();

        $totalAprovadosHojeCentavos = $aprovadosHoje->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        // ✅ Total aprovado nos últimos 7 dias
        $aprovadosSemana = BloobankWebhook::where('status', 'processed')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay()) // inclui hoje
            ->get();

        $totalAprovadosSemanaCentavos = $aprovadosSemana->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        return [
            Card::make('Pendentes Hoje', 'R$ ' . number_format($totalPendentesCentavos / 100, 2, ',', '.'))
                ->description('Total com status pending hoje')
                ->color('warning'),

            Card::make('Aprovados Hoje', 'R$ ' . number_format($totalAprovadosHojeCentavos / 100, 2, ',', '.'))
                ->description('Webhooks processados hoje')
                ->color('success'),

            Card::make('Aprovados na Semana', 'R$ ' . number_format($totalAprovadosSemanaCentavos / 100, 2, ',', '.'))
                ->description('Processados nos últimos 7 dias')
                ->color('success'),
        ];
    }
}
