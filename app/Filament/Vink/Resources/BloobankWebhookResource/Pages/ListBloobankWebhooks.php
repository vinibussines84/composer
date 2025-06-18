<?php

namespace App\Filament\Vink\Resources\BloobankWebhookResource\Pages;

use App\Filament\Vink\Resources\BloobankWebhookResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBloobankWebhooks extends ListRecords
{
    protected static string $resource = BloobankWebhookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
