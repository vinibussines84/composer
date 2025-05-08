<?php

namespace App\Filament\Xota\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use App\Filament\Xota\Resources\UserResource\Pages;
use Filament\Tables\Filters\Filter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuários';
    protected static ?string $navigationGroup = null;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nome')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('E-mail')
                ->required()
                ->email(),

            Toggle::make('cashin_ativo')
                ->label('CashIn Ativo')
                ->onColor('success')
                ->offColor('danger')
                ->default(true),

            Toggle::make('cashout_ativo')
                ->label('CashOut Ativo')
                ->onColor('success')
                ->offColor('danger')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),

                ToggleColumn::make('cashin_ativo')
                    ->label('CashIn Ativo')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger')
                    ->afterStateUpdated(fn ($record, $state) => $record->save()),

                ToggleColumn::make('cashout_ativo')
                    ->label('CashOut Ativo')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger')
                    ->afterStateUpdated(fn ($record, $state) => $record->save()),
            ])
            ->filters([
                Filter::make('buscar')
                    ->label('Nome ou E-mail')
                    ->form([
                        TextInput::make('value')->label('Buscar'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['value'], fn ($q, $value) =>
                            $q->where(fn ($sub) =>
                                $sub->where('name', 'like', "%{$value}%")
                                    ->orWhere('email', 'like', "%{$value}%")
                            )
                        )
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
