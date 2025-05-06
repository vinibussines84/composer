<?php

namespace App\Filament\Resources;

use App\Models\PixTransaction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Support\Facades\Auth;

class PixTransactionResource extends Resource
{
    protected static ?string $model = PixTransaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Extrato';

    public static function getModelLabel(): string
    {
        return 'Extrato';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Extrato';
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->query(fn () => PixTransaction::query()
                ->where('authkey', $user->authkey)
                ->where('gtkey', $user->gtkey)
                ->orderByDesc('created_at')
            )
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->getStateUsing(fn ($record) => $record->amount / 100)
                    ->money('BRL'),

                IconColumn::make('status')
                    ->label('Status')
                    ->icon(fn (string $state): string => match ($state) {
                        'waiting_payment' => 'heroicon-o-clock',
                        'pending' => 'heroicon-o-adjustments-horizontal',
                        'approved' => 'heroicon-o-check-circle',
                        'paid' => 'heroicon-o-check-badge',
                        'refused' => 'heroicon-o-x-circle',
                        'cancelled' => 'heroicon-o-ban',
                        'chargeback' => 'heroicon-o-arrow-uturn-left',
                        'in_protest' => 'heroicon-o-exclamation-triangle',
                        'refunded' => 'heroicon-o-arrow-path-rounded-square',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'approved', 'paid' => 'success',
                        'waiting_payment', 'pending' => 'warning',
                        'refused', 'cancelled', 'chargeback', 'in_protest', 'refunded' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at_api')
                    ->label('Data API')
                    ->dateTime(),

                TextColumn::make('created_at')
                    ->label('Recebido')
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'waiting_payment' => 'Aguardando pagamento',
                        'pending' => 'Pendente',
                        'approved' => 'Aprovado',
                        'paid' => 'Pago',
                        'refused' => 'Recusado',
                        'cancelled' => 'Cancelado',
                        'chargeback' => 'Chargeback',
                        'in_protest' => 'Em protesto',
                        'refunded' => 'Reembolsado',
                    ])
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PixTransactionResource\Pages\ListPixTransactions::route('/'),
        ];
    }
}
