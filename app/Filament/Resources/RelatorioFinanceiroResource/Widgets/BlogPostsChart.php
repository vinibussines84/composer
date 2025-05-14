<?php

namespace App\Filament\Resources\RelatorioFinanceiroResource\Widgets;

use App\Models\PixTransaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Total Recebido (últimas 24h)';
    protected int | string | array $columnSpan = 2;

    protected function getData(): array
    {
        $user = Auth::user();

        $total = PixTransaction::query()
            ->where('authkey', $user->authkey)
            ->where('gtkey', $user->gtkey)
            ->where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->sum('amount');

        // Converte de centavos para reais
        $total = $total / 100;

        return [
            'datasets' => [
                [
                    'label' => 'Recebido',
                    'data' => [$total],
                ],
            ],
            'labels' => ['Últimas 24h'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
