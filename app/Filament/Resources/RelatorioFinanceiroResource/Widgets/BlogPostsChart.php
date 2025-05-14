<?php

namespace App\Filament\Resources\RelatorioFinanceiroResource\Widgets;

use App\Models\PixTransaction;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Faturamento por período';
    protected int | string | array $columnSpan = 6;

    public ?string $startDate = null;
    public ?string $endDate = null;

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('startDate')
                ->label('Data inicial')
                ->default(Carbon::now()->subDays(6))
                ->reactive(),

            DatePicker::make('endDate')
                ->label('Data final')
                ->default(Carbon::now())
                ->reactive(),
        ];
    }

    protected function getData(): array
    {
        $user = Auth::user();

        $start = $this->startDate ? Carbon::parse($this->startDate) : Carbon::now()->subDays(6);
        $end = $this->endDate ? Carbon::parse($this->endDate) : Carbon::now();

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
