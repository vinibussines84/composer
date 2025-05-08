<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntegrationResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\HtmlString;

class IntegrationResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationLabel = 'Integração';
    protected static ?string $navigationGroup = 'Configurações';

    public static function getModelLabel(): string
    {
        return 'Chaves de Integração';
    }

    public static function getPluralModelLabel(): string
    {
        return '🔑 Chaves de Integração';
    }

    public static function getNavigationDescription(): ?string
    {
        return 'Utilize essas chaves para se integrar à sua conta via API';
    }

    public static function canCreate(): bool
    {
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
                TextColumn::make('authkey')
                    ->label('Auth Key')
                    ->copyable()
                    ->copyableState(fn ($record) => $record->authkey),

                TextColumn::make('gtkey')
                    ->label('G Key')
                    ->copyable()
                    ->copyableState(fn ($record) => $record->gtkey),
            ])
            ->actions([])
            ->bulkActions([])
            ->headerActions([])
            ->heading('Chaves de Integração')
            ->description(new HtmlString('<span class="text-sm text-gray-500">Clique para copiar suas credenciais.</span>'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIntegrations::route('/'),
        ];
    }
}
