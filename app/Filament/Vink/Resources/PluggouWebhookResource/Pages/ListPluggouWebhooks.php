<?php

namespace App\Filament\Vink\Resources\PluggouWebhookResource\Pages;

use App\Filament\Vink\Resources\PluggouWebhookResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPluggouWebhooks extends ListRecords
{
    protected static string $resource = PluggouWebhookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
