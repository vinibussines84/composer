<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Resources\Resource;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';  // ícone de menu (opcional)
    protected static ?string $navigationLabel = 'Usuários';          // rótulo no menu de navegação
    protected static ?string $modelLabel = 'Usuário';               // rótulo singular do modelo
    protected static ?string $pluralModelLabel = 'Usuários';        // rótulo plural do modelo

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
                ->label('Cashin Ativo')
                ->inline(false)  // exibe o label acima do toggle (padrão)
                ->default(true),
            Toggle::make('cashout_ativo')
                ->label('Cashout Ativo')
                ->inline(false)
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()   // permite busca por Nome
                    ->sortable(),    // permite ordenação por Nome
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()   // permite busca por E-mail
                    ->sortable(),
                ToggleColumn::make('cashin_ativo')
                    ->label('Cashin Ativo'),
                ToggleColumn::make('cashout_ativo')
                    ->label('Cashout Ativo'),
            ])
            ->filters([
                // (Opcional) filtros adicionais podem ser configurados aqui
            ])
            ->actions([
                Tables\Actions\EditAction::make(),     // ação de editar padrão
                // (Opcional) outras ações customizadas
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),  // ação em lote de exclusão
            ]);
            // Paginação padrão é automaticamente aplicada pelo Filament
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
