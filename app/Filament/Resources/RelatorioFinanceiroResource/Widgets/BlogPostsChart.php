<?php

namespace App\Filament\Resources\RelatorioFinanceiroResource\Widgets;

use App\Models\PixTransaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Auth;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = '📊 Faturamento por Período';
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
                ->label('📅 Data inicial')
                ->reactive(),

            DatePicker::make('endDate')
                ->label('📅 Data final')
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
                    'label' => '💰 Recebido',
                    'data' => $data->pluck('value'),
                    'tension' => 0.4, // Suaviza a linha
                    'fill' => true,
                    'backgroundColor' => 'rgba(34,197,94,0.2)', // Verde claro
                    'borderColor' => 'rgba(34,197,94,1)',       // Verde forte
                    'pointBackgroundColor' => 'white',
                    'pointBorderColor' => 'rgba(34,197,94,1)',
                    'pointRadius' => 5,
                    'pointHoverRadius' => 7,
                ],
            ],
            'labels' => $data->pluck('label'),
        ];
    }

    protected function getOptions(): ?array
    {
        return [
            'plugins' => [
                'tooltip' => [
                    'backgroundColor' => '#1e293b',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#facc15',
                    'callbacks' => [
                        'label' => \Illuminate\Support\Js::from(
                            <<<JS
                            function(context) {
                                let value = context.raw;
                                return '💸 R$ ' + value.toFixed(2).replace('.', ',');
                            }
                            JS
                        ),
                    ],
                ],
                'legend' => [
                    'labels' => [
                        'color' => '#334155',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold',
                        ],
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'color' => '#64748b',
                        'callback' => \Illuminate\Support\Js::from(
                            <<<JS
                            function(value) {
                                return 'R$ ' + value.toFixed(2).replace('.', ',');
                            }
                            JS
                        ),
                    ],
                    'grid' => [
                        'color' => 'rgba(203,213,225,0.3)',
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'color' => '#64748b',
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
