<?php

namespace App\Filament\Vink\Widgets;

use App\Models\BloobankWebhook;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class TotalPendentesWidget extends BaseWidget
{
    protected function getCards(): array
    {
        // Busca todos os webhooks com status "pending"
        $pendentes = BloobankWebhook::where('status', 'pending')->get();

        // Soma o valor total dos pendentes
        $totalCentavos = $pendentes->sum(function ($webhook) {
            $payload = json_decode($webhook->payload, true);
            return $payload['body']['amount']['value'] ?? 0;
        });

        $total = $totalCentavos / 100;

        return [
            Card::make('Total Pendentes', 'R$ ' . number_format($total, 2, ',', '.'))
                ->description('Somatório de valores com status pending')
                ->color('warning'),
        ];
    }
}
