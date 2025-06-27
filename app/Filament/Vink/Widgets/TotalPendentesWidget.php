<?php

namespace App\Filament\Vink\Widgets;

use App\Models\BloobankWebhook;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class TotalPendentesWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $hoje = Carbon::today();
        $inicioSemana = Carbon::now()->startOfWeek();

        // Total Pendentes HOJE
        $pendentesHoje = BloobankWebhook::where('status', 'pending')
            ->whereDate('created_at', $hoje)
            ->get();

        $totalPendentesHoje = $pendentesHoje->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        // Pagos HOJE (status: approved)
        $pagosHoje = BloobankWebhook::whereDate('created_at', $hoje)
            ->get()
            ->filter(function ($webhook) {
                $payload = json_decode($webhook->payload, true);
                return ($payload['body']['status'] ?? null) === 'approved';
            });

        $totalPagosHoje = $pagosHoje->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        // Pagos na SEMANA (status: approved)
        $pagosSemana = BloobankWebhook::whereDate('created_at', '>=', $inicioSemana)
            ->get()
            ->filter(function ($webhook) {
                $payload = json_decode($webhook->payload, true);
                return ($payload['body']['status'] ?? null) === 'approved';
            });

        $totalPagosSemana = $pagosSemana->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        return [
            Card::make('Total Pendentes Hoje', 'R$ ' . number_format($totalPendentesHoje / 100, 2, ',', '.'))
                ->description('Status pending de hoje')
                ->color('warning'),

            Card::make('Total Pagos Hoje', 'R$ ' . number_format($totalPagosHoje / 100, 2, ',', '.'))
                ->description('Webhooks approved hoje')
                ->color('success'),

            Card::make('Total Pagos na Semana', 'R$ ' . number_format($totalPagosSemana / 100, 2, ',', '.'))
                ->description('Desde segunda-feira')
                ->color('primary'),
        ];
    }
}
