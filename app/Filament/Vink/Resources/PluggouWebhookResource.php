<?php

namespace App\Filament\Vink\Resources;

use App\Filament\Vink\Resources\PluggouWebhookResource\Pages;
use App\Models\PluggouWebhook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class PluggouWebhookResource extends Resource
{
    protected static ?string $model = PluggouWebhook::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationGroup = 'Vink';
    protected static ?string $navigationLabel = 'Webhooks Pluggou';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('payload')
                ->label('Payload')
                ->rows(10)
                ->disabled(),

            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'processed' => 'Processed',
                    'error' => 'Error',
                ])
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),

                Tables\Columns\TextColumn::make('reference_code')
                    ->label('Ref')
                    ->getStateUsing(fn (PluggouWebhook $record) =>
                        $record->payload['data']['referenceCode'] ?? '-'),

                Tables\Columns\TextColumn::make('customer')
                    ->label('Cliente')
                    ->getStateUsing(fn (PluggouWebhook $record) =>
                        $record->payload['data']['customerName'] ?? '-'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->getStateUsing(fn (PluggouWebhook $record) =>
                        isset($record->payload['data']['amount'])
                            ? 'R$ ' . number_format($record->payload['data']['amount'], 2, ',', '.')
                            : '-'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'processed' => 'success',
                        'error' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')->since(),
            ])
            ->actions([
                Action::make('Aprovar')
                    ->visible(fn (PluggouWebhook $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->color('success')
                    ->action(function (PluggouWebhook $record) {
                        try {
                            (new \App\Services\PluggouWebhookProcessor())->process($record->payload);

                            $record->update(['status' => 'processed']);

                            Notification::make()
                                ->title('✅ Webhook processado com sucesso')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            $record->update(['status' => 'error']);

                            Notification::make()
                                ->title('❌ Erro ao processar webhook')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->headerActions([
                Action::make('alternar-processamento-automatico')
                    ->label(fn () => cache('pluggou.auto_process', false)
                        ? '🔴 Desativar Automático'
                        : '🟢 Ativar Automático')
                    ->color(fn () => cache('pluggou.auto_process', false) ? 'danger' : 'success')
                    ->form([
                        TextInput::make('pin')
                            ->label('Digite o PIN')
                            ->password()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $pinCorreto = '2244';

                        if ($data['pin'] !== $pinCorreto) {
                            Notification::make()
                                ->title('❌ PIN incorreto')
                                ->body('Você não tem permissão para ativar essa função.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $atual = cache('pluggou.auto_process', false);
                        cache()->forever('pluggou.auto_process', ! $atual);

                        Notification::make()
                            ->title($atual
                                ? '🛑 Processamento automático desativado'
                                : '✅ Processamento automático ativado')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPluggouWebhooks::route('/'),
        ];
    }
}
