<?php

namespace App\Filament\Resources\RelatorioFinanceiroResource\Widgets;

use App\Models\PixTransaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Faturamento da Semana';

    // ⬇️ Ocupar 1/3 da largura total da linha
    protected int | string | array $columnSpan = 4;

    protected function getData(): array
    {
        $user = Auth::user();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $data = collect();

        for ($date = $startOfWeek->copy(); $date <= $endOfWeek; $date->addDay()) {
            $total = PixTransaction::query()
                ->where('authkey', $user->authkey)
                ->where('gtkey', $user->gtkey)
                ->where('status', 'paid')
                ->whereDate('created_at', $date->toDateString())
                ->sum('amount');

            $data->push([
                'label' => $date->format('d/m'),
                'value' => $total / 100,
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
        return 'line';
    }
}
