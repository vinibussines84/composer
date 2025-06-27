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
        $inicioSemana = Carbon::now()->startOfWeek();

        /**
         * 🔸 Total Pendentes Hoje
         */
        $pendentesHoje = BloobankWebhook::where('status', 'pending')
            ->whereDate('created_at', $hoje)
            ->get();

        $valorPendentesHoje = $pendentesHoje->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        $qtdPendentesHoje = $pendentesHoje->count();

        /**
         * ✅ Total Pagos Hoje (status 'approved' no payload)
         */
        $aprovadosHoje = BloobankWebhook::whereDate('created_at', $hoje)
            ->get()
            ->filter(fn ($webhook) => (json_decode($webhook->payload, true)['body']['status'] ?? null) === 'approved');

        $valorAprovadosHoje = $aprovadosHoje->sum(fn ($webhook) => json_decode($webhook->payload, true)['body']['amount']['value'] ?? 0);
        $qtdAprovadosHoje = $aprovadosHoje->count();

        /**
         * 📆 Total Pagos na Semana
         */
        $aprovadosSemana = BloobankWebhook::whereDate('created_at', '>=', $inicioSemana)
            ->get()
            ->filter(fn ($webhook) => (json_decode($webhook->payload, true)['body']['status'] ?? null) === 'approved');

        $valorAprovadosSemana = $aprovadosSemana->sum(fn ($webhook) => json_decode($webhook->payload, true)['body']['amount']['value'] ?? 0);
        $qtdAprovadosSemana = $aprovadosSemana->count();

        /**
         * 👤 Gerados por Vinicius (PixTransaction com user_id = 27)
         */
        $geradosViniciusHoje = PixTransaction::where('user_id', 27)
            ->whereDate('created_at', $hoje)
            ->get();

        $valorGeradosVinicius = $geradosViniciusHoje->sum('amount');
        $qtdGeradosVinicius = $geradosViniciusHoje->count();

        /**
         * ✅ Pagos por Vinicius (CashIn efetivado com status 'paid')
         */
        $pagosVinicius = PixTransaction::where('user_id', 27)
            ->where('status', 'paid')
            ->get();

        $valorPagosVinicius = $pagosVinicius->sum('amount');
        $qtdPagosVinicius = $pagosVinicius->count();

        /**
         * 🔁 Cards
         */
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
                ->description($qtdGeradosVinicius . ' criados hoje')
                ->color('gray'),

            Card::make('Pagos Vinicius', 'R$ ' . number_format($valorPagosVinicius / 100, 2, ',', '.'))
                ->description($qtdPagosVinicius . ' pagos (Cash In)')
                ->color('emerald'),
        ];
    }
}
