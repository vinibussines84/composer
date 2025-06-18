<?php

namespace App\Filament\Vink\Resources;

use App\Filament\Vink\Resources\BloobankWebhookResource\Pages;
use App\Models\BloobankWebhook;
use App\Services\BloobankWebhookProcessor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class BloobankWebhookResource extends Resource
{
    protected static ?string $model = BloobankWebhook::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationGroup = 'Vink';
    protected static ?string $navigationLabel = 'Webhooks Bloobank';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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

                Tables\Columns\TextColumn::make('nickname')
                    ->label('Nickname')
                    ->getStateUsing(function (BloobankWebhook $record) {
                        $data = json_decode($record->payload, true);
                        return $data['body']['customer']['nickname'] ?? '-';
                    }),

                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->getStateUsing(function (BloobankWebhook $record) {
                        $data = json_decode($record->payload, true);
                        return $data['body']['id'] ?? '-';
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount (Value)')
                    ->getStateUsing(function (BloobankWebhook $record) {
                        $data = json_decode($record->payload, true);
                        return isset($data['body']['amount']['value']) 
                            ? number_format($data['body']['amount']['value'] / 100, 2, ',', '.') . ' BRL'
                            : '-';
                    }),

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
                    ->visible(fn (BloobankWebhook $record) => $record->status === 'pending')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (BloobankWebhook $record) {
                        try {
                            $data = json_decode($record->payload, true);

                            (new BloobankWebhookProcessor())->process($data);

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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBloobankWebhooks::route('/'),
        ];
    }
}
