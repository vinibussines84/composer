<?php

namespace App\Filament\Vink\Resources;

use App\Filament\Vink\Resources\PluggouWebhookResource\Pages;
use App\Models\PluggouWebhook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

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

                Tables\Columns\IconColumn::make('automatic_processing')
                    ->label('Auto')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->color(fn (bool $state) => $state ? 'success' : 'gray'),

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

                Action::make('Ativar Automático')
                    ->icon('heroicon-o-play-circle')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (PluggouWebhook $record) => $record->automatic_processing === false)
                    ->action(function (PluggouWebhook $record) {
                        $record->update(['automatic_processing' => true]);

                        Notification::make()
                            ->title('✅ Processamento automático ativado')
                            ->success()
                            ->send();
                    }),

                Action::make('Desativar Automático')
                    ->icon('heroicon-o-pause-circle')
                    ->color('secondary')
                    ->requiresConfirmation()
                    ->visible(fn (PluggouWebhook $record) => $record->automatic_processing === true)
                    ->action(function (PluggouWebhook $record) {
                        $record->update(['automatic_processing' => false]);

                        Notification::make()
                            ->title('🛑 Processamento automático desativado')
                            ->warning()
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
