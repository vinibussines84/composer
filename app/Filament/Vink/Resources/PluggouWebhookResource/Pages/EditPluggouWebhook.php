<?php

namespace App\Filament\Vink\Resources\PluggouWebhookResource\Pages;

use App\Filament\Vink\Resources\PluggouWebhookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPluggouWebhook extends EditRecord
{
    protected static string $resource = PluggouWebhookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
