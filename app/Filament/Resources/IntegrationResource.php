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
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        return new HtmlString('
                            <span 
                                x-data="{ copied: false }" 
                                x-init="$el.addEventListener(\'click\', async () => {
                                    await navigator.clipboard.writeText(\'' . e($record->authkey) . '\');
                                    copied = true;
                                    setTimeout(() => copied = false, 2000);
                                })"
                                class="cursor-pointer text-sm text-gray-800 hover:underline"
                            >
                                <span x-show="!copied">' . substr($record->authkey, 0, 4) . '•••••••• 👁️</span>
                                <span x-show="copied" class="text-green-600 font-semibold">Copiado!</span>
                            </span>
                        ');
                    }),

                TextColumn::make('gtkey')
                    ->label('G Key')
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        return new HtmlString('
                            <span 
                                x-data="{ copied: false }" 
                                x-init="$el.addEventListener(\'click\', async () => {
                                    await navigator.clipboard.writeText(\'' . e($record->gtkey) . '\');
                                    copied = true;
                                    setTimeout(() => copied = false, 2000);
                                })"
                                class="cursor-pointer text-sm text-gray-800 hover:underline"
                            >
                                <span x-show="!copied">' . substr($record->gtkey, 0, 4) . '•••••••• 👁️</span>
                                <span x-show="copied" class="text-green-600 font-semibold">Copiado!</span>
                            </span>
                        ');
                    }),
            ])
            ->actions([])
            ->bulkActions([])
            ->headerActions([])
            ->heading('Chaves de Integração')
            ->description(new HtmlString('<span class="text-sm text-gray-500">Clique em suas credenciais para copiar.</span>'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIntegrations::route('/'),
        ];
    }
}
