<?php

namespace App\Filament\Resources\RelatorioFinanceiroResource\Widgets;

use App\Models\PixTransaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Faturamento dos últimos 7 dias';

    // ⬇️ Ocupar 1/4 da linha, sem ultrapassar o layout
    protected int | string | array $columnSpan = 3;

    protected function getData(): array
    {
        $user = Auth::user();

        $today = Carbon::today();
        $startDate = $today->copy()->subDays(6); // últimos 7 dias, incluindo hoje

        $data = collect();

        for ($date = $startDate->copy(); $date <= $today; $date->addDay()) {
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
                    'label' => 'Recebido',
                    'data' => $data->pluck('value'),
                    'fill' => true,
                    'tension' => 0.4,
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
