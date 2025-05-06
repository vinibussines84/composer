<?php

namespace App\Filament\Xota\Resources;

use App\Filament\Xota\Resources\PixTransactionResource\Pages;
use App\Models\PixTransaction;
use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;

class PixTransactionResource extends Resource
{
    protected static ?string $model = PixTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Transações Pix';
    protected static ?string $navigationGroup = null;

    public static function form(Form $form): Form
    {
        return $form->schema([]); // Apenas visualização
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),

                TextColumn::make('user.name')->label('Usuário')->searchable(),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),

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
                        'waiting_payment', 'pending' => 'warning',
                        'approved', 'paid' => 'success',
                        'refused', 'cancelled', 'chargeback' => 'danger',
                        'in_protest' => 'gray',
                        'refunded' => 'info',
                        default => 'secondary',
                    })
                    ->tooltip(fn (string $state): string => match ($state) {
                        'waiting_payment' => 'Aguardando pagamento',
                        'pending' => 'Em processo de confirmação',
                        'approved' => 'Pagamento aprovado',
                        'paid' => 'Pagamento confirmado',
                        'refused' => 'Pagamento recusado',
                        'cancelled' => 'Transação cancelada',
                        'chargeback' => 'Estorno realizado',
                        'in_protest' => 'Em contestação',
                        'refunded' => 'Pagamento reembolsado',
                        default => 'Status desconhecido',
                    })
                    ->sortable(),

                TextColumn::make('created_at_api')
                    ->label('Criado na API')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Usuário')
                    ->options(User::pluck('name', 'id')->toArray()),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'waiting_payment' => 'Aguardando pagamento',
                        'pending' => 'Em processo de confirmação',
                        'approved' => 'Pagamento aprovado',
                        'paid' => 'Pagamento confirmado',
                        'refused' => 'Pagamento recusado',
                        'in_protest' => 'Em contestação',
                        'refunded' => 'Pagamento reembolsado',
                        'cancelled' => 'Transação cancelada',
                        'chargeback' => 'Estorno realizado',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPixTransactions::route('/'),
        ];
    }
}
