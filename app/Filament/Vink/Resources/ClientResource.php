<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BloobankWebhookResource\Pages;
use App\Models\BloobankWebhook;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;

class BloobankWebhookResource extends Resource
{
    protected static ?string $model = BloobankWebhook::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Vink';  // <<< Aqui define o grupo de menu

    protected static ?string $navigationLabel = 'Webhooks Bloobank';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('payload')->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processed' => 'Processed',
                        'error' => 'Error',
                    ])
                    ->disabled(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                Action::make('Aprovar e Processar')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        try {
                            $payload = json_decode($record->payload, true);
                            $data = $payload['body'] ?? [];
                            app(\App\Services\BloobankWebhookProcessor::class)->process($data);
                            $record->update(['status' => 'processed']);
                        } catch (\Throwable $e) {
                            $record->update(['status' => 'error']);
                            \Log::error('Erro ao processar webhook manual: ' . $e->getMessage());
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBloobankWebhooks::route('/'),
        ];
    }
}
