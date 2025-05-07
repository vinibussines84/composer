<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntegrationResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class IntegrationResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationLabel = 'Integração';
    protected static ?string $navigationGroup = 'Configurações';

    public static function getModelLabel(): string
    {
        return 'Chaves de Integrações';
    }

    public static function getPluralModelLabel(): string
    {
        return '🔑 Chaves de Integrações';
    }

    public static function getNavigationDescription(): ?string
    {
        return 'Utilize essas chaves para se integrar à sua conta via API';
    }

    public static function canCreate(): bool
    {
        // Impede que o botão “Criar” seja exibido
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn () => User::query()->where('id', Auth::id()))
            ->columns([
                Tables\Columns\TextColumn::make('authkey')
                    ->label('Auth Key')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 4) . '•••••••• 👁️')
                    ->copyable()
                    ->tooltip('Clique para copiar a chave completa'),

                Tables\Columns\TextColumn::make('gtkey')
                    ->label('G Key')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 4) . '•••••••• 👁️')
                    ->copyable()
                    ->tooltip('Clique para copiar a chave completa'),
            ])
            // Remove ações de linha
            ->actions([])
            // Remove ações em massa
            ->bulkActions([])
            // Remove o botão “Criar” no cabeçalho
            ->headerActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIntegrations::route('/'),
            // criação e edição desabilitadas
            // 'create' => Pages\CreateIntegration::route('/create'),
            // 'edit'   => Pages\EditIntegration::route('/{record}/edit'),
        ];
    }
}
