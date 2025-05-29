<?php

namespace App\Filament\Resources\RelatorioFinanceiroResource\Widgets;

use App\Models\PixTransaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Js;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = '📊 Faturamento por Período (R$)';
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

        $labels = [];
        $values = [];

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $total = PixTransaction::query()
                ->where('authkey', $user->authkey)
                ->where('gtkey', $user->gtkey)
                ->where('status', 'paid')
                ->whereDate('created_at', $date->toDateString())
                ->sum('amount');

            $labels[] = $date->format('d/m');
            $values[] = $total / 100; // já em reais
        }

        return [
            'datasets' => [
                [
                    'label' => 'Recebido',
                    'data' => $values,
                    'fill' => true,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): ?array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => Js::from(<<<'JS'
                            function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'R$ ' + context.parsed.y.toFixed(2).replace('.', ',');
                                }
                                return label;
                            }
                        JS),
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => Js::from(<<<'JS'
                            function(value) {
                                return 'R$ ' + value.toFixed(2).replace('.', ',');
                            }
                        JS),
                    ],
                ],
            ],
        ];
    }
}
