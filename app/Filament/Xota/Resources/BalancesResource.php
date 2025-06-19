<?php

namespace App\Filament\Xota\Resources;

use App\Filament\Xota\Resources\BalancesResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class BalancesResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Saldo Clientes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('saldo')
                    ->label('Saldo')
                    ->prefix('R$')
                    ->mask(RawJs::make('$money($input)'))
                    ->numeric()
                    ->dehydrateStateUsing(fn ($state) => {
                        $cleaned = preg_replace('/[^0-9,\.]/', '', $state);

                        if (strpos($cleaned, ',') !== false) {
                            $cleaned = str_replace('.', '', $cleaned);
                            $cleaned = str_replace(',', '.', $cleaned);
                        } else {
                            $cleaned = str_replace(',', '', $cleaned);
                        }

                        return (int) round(floatval($cleaned) * 100);
                    })
                    ->formatStateUsing(fn ($state) => number_format($state / 100, 2, ',', '.')),

                TextInput::make('bloqueado')
                    ->label('Bloqueado')
                    ->prefix('R$')
                    ->mask(RawJs::make('$money($input)'))
                    ->numeric()
                    ->dehydrateStateUsing(fn ($state) => {
                        $cleaned = preg_replace('/[^0-9,\.]/', '', $state);

                        if (strpos($cleaned, ',') !== false) {
                            $cleaned = str_replace('.', '', $cleaned);
                            $cleaned = str_replace(',', '.', $cleaned);
                        } else {
                            $cleaned = str_replace(',', '', $cleaned);
                        }

                        return (int) round(floatval($cleaned) * 100);
                    })
                    ->formatStateUsing(fn ($state) => number_format($state / 100, 2, ',', '.')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable(),
                TextColumn::make('email')->label('E-mail')->searchable(),

                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->getStateUsing(fn ($record) => $record->saldo / 100)
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('bloqueado')
                    ->label('Bloqueado')
                    ->getStateUsing(fn ($record) => $record->bloqueado / 100)
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('saldo_disponivel')
                    ->label('Disponível')
                    ->getStateUsing(fn ($record) => ($record->saldo - $record->bloqueado) / 100)
                    ->money('BRL')
                    ->sortable()
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBalances::route('/'),
            'edit' => Pages\EditBalances::route('/{record}/edit'),
        ];
    }
}
