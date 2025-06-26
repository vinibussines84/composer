<?php

namespace App\Filament\Vink\Resources\BloobankWebhookResource\Pages;

use App\Filament\Vink\Resources\BloobankWebhookResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class ListBloobankWebhooks extends ListRecords
{
    protected static string $resource = BloobankWebhookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleAutoProcess')
                ->label(Cache::get('bloobank_auto_process', false) ? 'Desativar auto-processo' : 'Ativar auto-processo')
                ->action(function () {
                    $current = Cache::get('bloobank_auto_process', false);
                    Cache::put('bloobank_auto_process', ! $current);

                    Notification::make()
                        ->title('Configuração atualizada')
                        ->body('Processamento automático ' . (!$current ? 'ativado' : 'desativado') . '.')
                        ->success()
                        ->send();

                    $this->redirect($this->getUrl());
                })
                ->color(Cache::get('bloobank_auto_process', false) ? 'danger' : 'success')
                ->icon(Cache::get('bloobank_auto_process', false) ? 'heroicon-o-x-mark' : 'heroicon-o-check'),
        ];
    }
}
