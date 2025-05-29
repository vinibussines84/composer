<?php

namespace App\Filament\Resources\RelatorioFinanceiroResource\Widgets;

use App\Models\PixTransaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Auth;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = '📈 Faturamento diário (R$)';
    protected int | string | array $columnSpan = 2;

    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = now()->subDays(6)->toDateString();
        $this->endDate = now()->toDateString();
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('startDate')
                ->label('Data inicial')
                ->reactive(),

            DatePicker::make('endDate')
                ->label('Data final')
                ->reactive(),
        ];
    }

    protected function getData(): array
    {
        $user = Auth::user();

        $start = $this->startDate ? Carbon::parse($this->startDate) : now()->subDays(6);
        $end = $this->endDate ? Carbon::parse($this->endDate) : now();

        $data = collect();

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
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
                    'label' => 'Faturamento',
                    'data' => $data->pluck('value'),
                    'fill' => true,
                    'borderWidth' => 2,
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

    protected function getOptions(): ?array
    {
        return [
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => \Illuminate\Support\Js::from([
                            'function(context) { return "R$ " + context.parsed.y.toLocaleString("pt-BR", {minimumFractionDigits: 2}); }'
                        ]),
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => \Illuminate\Support\Js::from([
                            'function(value) { return "R$ " + value.toLocaleString("pt-BR", {minimumFractionDigits: 2}); }'
                        ]),
                    ],
                ],
            ],
        ];
    }
}
