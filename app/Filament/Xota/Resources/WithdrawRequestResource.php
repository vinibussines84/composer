<?php

namespace App\Filament\Xota\Resources;

use App\Filament\Xota\Resources\WithdrawRequestResource\Pages;
use App\Models\WithdrawRequest;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;

class WithdrawRequestResource extends Resource
{
    protected static ?string $model = WithdrawRequest::class;

    protected static ?string $navigationLabel = 'Saque';
    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->orderByDesc('created_at')) // <- Ordenar pelos mais recentes
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Usuário'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->getStateUsing(fn ($record) => $record->amount / 100)
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('pix_type')->label('Tipo de chave'),
                Tables\Columns\TextColumn::make('pix_key')->label('Chave PIX'),
                IconColumn::make('status')
                    ->label('Status')
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'autorizado' => 'heroicon-o-check-circle',
                        'cancelado' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'autorizado' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Solicitado em')->since(),
            ])
            ->actions([
                Action::make('autorizar')
                    ->label('Autorizar')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'autorizado']);
                    }),

                Action::make('cancelar')
                    ->label('Cancelar')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $user = $record->user;
                        $user->increment('saldo', $record->amount);
                        $record->update(['status' => 'cancelado']);
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWithdrawRequests::route('/'),
        ];
    }
}
