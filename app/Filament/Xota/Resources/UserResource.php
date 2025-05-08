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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use App\Filament\Xota\Resources\UserResource\Pages;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

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
                TextInput::make('gtkey')->label('GtKey'), //kk
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
                TextColumn::make('name')->label('Nome')->searchable(),
                TextColumn::make('email')->label('E-mail')->searchable(),

                TextColumn::make('taxa_cash_in')
                    ->label('CashIn')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),

                TextColumn::make('taxa_cash_out')
                    ->label('CashOut')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),

                ToggleColumn::make('cashin_ativo')
                    ->label('CashIn')
                    ->onIcon('heroicon-o-check-circle')
                    ->offIcon('heroicon-o-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->alignRight(),

                ToggleColumn::make('cashout_ativo')
                    ->label('CashOut')
                    ->onIcon('heroicon-o-check-circle')
                    ->offIcon('heroicon-o-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->alignRight(),

                TextColumn::make('webhookcashin')->label('Webhook CashIn')->limit(30)->toggleable(),
                TextColumn::make('webhookcashout')->label('Webhook CashOut')->limit(30)->toggleable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('name')
                    ->label('Buscar por Nome')
                    ->form([
                        TextInput::make('value')->label('Nome')->placeholder('Digite o nome'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['value'], fn ($q, $value) => $q->where('name', 'like', "%{$value}%"));
                    }),

                SelectFilter::make('email')
                    ->label('Filtrar por E-mail')
                    ->options(fn () => User::query()
                        ->select('email')
                        ->distinct()
                        ->orderBy('email')
                        ->pluck('email', 'email')
                        ->toArray()),
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
