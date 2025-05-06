<?php

namespace App\Filament\Xota\Resources\UserResource\Pages;

use App\Filament\Xota\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testarWebhook')
                ->label('Testar Webhook CashIn')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->action(function () {
                    $url = $this->record->webhookcashin;

                    if (! $url) {
                        Notification::make()
                            ->title('Webhook CashIn não está configurado.')
                            ->warning()
                            ->send();
                        return;
                    }

                    try {
                        $response = Http::post($url, [
                            'teste' => true,
                            'mensagem' => 'Webhook CashIn funcionando!',
                        ]);

                        Notification::make()
                            ->title("Resposta: HTTP " . $response->status())
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title("Erro: " . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
