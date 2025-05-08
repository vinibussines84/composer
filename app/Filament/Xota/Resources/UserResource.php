<?php

namespace App\Filament\Xota\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Filament\Xota\Resources\UserResource\Pages;
use Filament\Tables\Filters\Filter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Usuários';
    protected static ?string $navigationGroup = null;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Nome')->required(),
                TextInput::make('email')->label('E-mail')->email()->required(),
                TextInput::make('taxa_cash_in')->label('Taxa CashIn %')->numeric()->inputMode('decimal'),
                TextInput::make('taxa_cash_out')->label('Taxa CashOut %')->numeric()->inputMode('decimal'),
                TextInput::make('authkey')->label('AuthKey'),
                TextInput::make('gtkey')->label('GtKey'),
                TextInput::make('senha')->label('Senha'),

                Toggle::make('cashin_ativo')
                    ->label('CashIn')
                    ->onColor('success')
                    ->offColor('danger')
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-mark'),

                Toggle::make('cashout_ativo')
                    ->label('CashOut')
                    ->onColor('success')
                    ->offColor('danger')
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-mark'),

                TextInput::make('webhookcashin')
                    ->label('Webhook CashIn')
                    ->url()
                    ->suffixIcon('heroicon-o-link'),

                TextInput::make('webhookcashout')
                    ->label('Webhook CashOut')
                    ->url()
                    ->suffixIcon('heroicon-o-link'),

                TextInput::make('created_at')
                    ->label('Criado em')
                    ->disabled()
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('d/m/Y H:i')),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->searchable()->sortable(),

                TextColumn::make('taxa_cash_in')
                    ->label('Taxa CashIn')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),

                TextColumn::make('taxa_cash_out')
                    ->label('Taxa CashOut')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),

                ToggleColumn::make('cashin_ativo')
                    ->label('CashIn')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable()
                    ->afterStateUpdated(fn ($record, $state) => $record->save()),

                ToggleColumn::make('cashout_ativo')
                    ->label('CashOut')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable()
                    ->afterStateUpdated(fn ($record, $state) => $record->save()),

                TextColumn::make('webhookcashin')->label('Webhook CashIn')->limit(30)->toggleable(),
                TextColumn::make('webhookcashout')->label('Webhook CashOut')->limit(30)->toggleable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('buscar')
                    ->label('Buscar por Nome ou E-mail')
                    ->form([
                        TextInput::make('value')->label('Buscar')->placeholder('Digite nome ou e-mail'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['value'], function ($q, $value) {
                            $q->where(fn ($sub) =>
                                $sub->where('name', 'like', "%{$value}%")
                                    ->orWhere('email', 'like', "%{$value}%")
                            );
                        });
                    }),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
