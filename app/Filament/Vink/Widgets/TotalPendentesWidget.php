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

        // 🔸 Pendentes hoje
        $pendentesHoje = BloobankWebhook::where('status', 'pending')
            ->whereDate('created_at', $hoje)
            ->get();

        $valorPendentesHoje = $pendentesHoje->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        $qtdPendentesHoje = $pendentesHoje->count();

        // ✅ Aprovados hoje
        $aprovadosHoje = BloobankWebhook::whereDate('created_at', $hoje)
            ->get()
            ->filter(function ($webhook) {
                $payload = json_decode($webhook->payload, true);
                return ($payload['body']['status'] ?? null) === 'approved';
            });

        $valorAprovadosHoje = $aprovadosHoje->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        $qtdAprovadosHoje = $aprovadosHoje->count();

        // 📆 Aprovados na semana
        $aprovadosSemana = BloobankWebhook::whereDate('created_at', '>=', $inicioSemana)
            ->get()
            ->filter(function ($webhook) {
                $payload = json_decode($webhook->payload, true);
                return ($payload['body']['status'] ?? null) === 'approved';
            });

        $valorAprovadosSemana = $aprovadosSemana->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        $qtdAprovadosSemana = $aprovadosSemana->count();

        return [
            Card::make('Pendentes Hoje', 'R$ ' . number_format($valorPendentesHoje / 100, 2, ',', '.'))
                ->description($qtdPendentesHoje . ' transações pendentes')
                ->color('warning'),

            Card::make('Pagos Hoje', 'R$ ' . number_format($valorAprovadosHoje / 100, 2, ',', '.'))
                ->description($qtdAprovadosHoje . ' transações aprovadas')
                ->color('success'),

            Card::make('Pagos na Semana', 'R$ ' . number_format($valorAprovadosSemana / 100, 2, ',', '.'))
                ->description($qtdAprovadosSemana . ' transações aprovadas')
                ->color('primary'),
        ];
    }
}
