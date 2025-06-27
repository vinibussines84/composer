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

        // Total Pendentes HOJE (status na coluna)
        $pendentesHoje = BloobankWebhook::where('status', 'pending')
            ->whereDate('created_at', $hoje)
            ->get();

        $totalPendentesHoje = $pendentesHoje->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        // Pagos HOJE (status dentro do payload JSON)
        $pagosHoje = BloobankWebhook::whereDate('created_at', $hoje)
            ->get()
            ->filter(function ($webhook) {
                $payload = json_decode($webhook->payload, true);
                return ($payload['body']['status'] ?? null) === 'paid';
            });

        $totalPagosHoje = $pagosHoje->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        // Pagos na SEMANA (status dentro do payload JSON)
        $pagosSemana = BloobankWebhook::whereDate('created_at', '>=', $inicioSemana)
            ->get()
            ->filter(function ($webhook) {
                $payload = json_decode($webhook->payload, true);
                return ($payload['body']['status'] ?? null) === 'paid';
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
                ->description('Webhooks pagos hoje')
                ->color('success'),

            Card::make('Total Pagos na Semana', 'R$ ' . number_format($totalPagosSemana / 100, 2, ',', '.'))
                ->description('Desde segunda-feira')
                ->color('primary'),
        ];
    }
}
