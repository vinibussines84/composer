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
        $viniciusId = '27';

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
            ->filter(fn ($webhook) => (json_decode($webhook->payload, true)['body']['status'] ?? null) === 'approved');

        $valorAprovadosHoje = $aprovadosHoje->sum(fn ($webhook) => json_decode($webhook->payload, true)['body']['amount']['value'] ?? 0);
        $qtdAprovadosHoje = $aprovadosHoje->count();

        // 📆 Aprovados na semana
        $aprovadosSemana = BloobankWebhook::whereDate('created_at', '>=', $inicioSemana)
            ->get()
            ->filter(fn ($webhook) => (json_decode($webhook->payload, true)['body']['status'] ?? null) === 'approved');

        $valorAprovadosSemana = $aprovadosSemana->sum(fn ($webhook) => json_decode($webhook->payload, true)['body']['amount']['value'] ?? 0);
        $qtdAprovadosSemana = $aprovadosSemana->count();

        // 👤 Gerados por Vinicius (user_id: 27)
        $geradosVinicius = BloobankWebhook::get()->filter(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return (string) ($payload['body']['metadata']['user_id'] ?? '') === '27';
        });

        $valorGeradosVinicius = $geradosVinicius->sum(fn ($webhook) => json_decode($webhook->payload, true)['body']['amount']['value'] ?? 0);
        $qtdGeradosVinicius = $geradosVinicius->count();

        // ✅ Pagos por Vinicius
        $pagosVinicius = $geradosVinicius->filter(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return ($payload['body']['status'] ?? null) === 'approved';
        });

        $valorPagosVinicius = $pagosVinicius->sum(fn ($webhook) => json_decode($webhook->payload, true)['body']['amount']['value'] ?? 0);
        $qtdPagosVinicius = $pagosVinicius->count();

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

            Card::make('Gerados Vinicius', 'R$ ' . number_format($valorGeradosVinicius / 100, 2, ',', '.'))
                ->description($qtdGeradosVinicius . ' webhooks gerados')
                ->color('gray'),

            Card::make('Pagos Vinicius', 'R$ ' . number_format($valorPagosVinicius / 100, 2, ',', '.'))
                ->description($qtdPagosVinicius . ' aprovados de Vinicius')
                ->color('emerald'),
        ];
    }
}
