<?php

namespace App\Filament\Resources\RelatorioFinanceiroResource\Widgets;

use App\Models\PixTransaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Faturamento da Semana';
    protected int | string | array $columnSpan = 2;

    protected function getData(): array
    {
        $user = Auth::user();

        $startOfWeek = Carbon::now()->startOfWeek(); // Segunda
        $endOfWeek = Carbon::now()->endOfWeek();     // Domingo

        $data = collect();

        // Gera uma linha para cada dia da semana (Seg a Dom)
        for ($date = $startOfWeek->copy(); $date <= $endOfWeek; $date->addDay()) {
            $total = PixTransaction::query()
                ->where('authkey', $user->authkey)
                ->where('gtkey', $user->gtkey)
                ->where('status', 'paid')
                ->whereDate('created_at', $date->toDateString())
                ->sum('amount');

            $data->push([
                'label' => $date->format('d/m'),
                'value' => $total / 100, // Centavos para reais
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Recebido por dia',
                    'data' => $data->pluck('value'),
                ],
            ],
            'labels' => $data->pluck('label'),
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Agora é gráfico de linha
    }
}
//kk