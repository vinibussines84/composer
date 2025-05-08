<?php

namespace App\Filament\Xota\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class SaldoClientesWidget extends BaseWidget
{
    public static ?string $heading = '💰 Saldo de Clientes';
    protected static ?int $sort = 2;

    protected function getTableQuery(): Builder
    {
        return User::query()->orderByDesc('saldo');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Nome')
                ->searchable(),

            TextColumn::make('email')
                ->label('E-mail')
                ->searchable()
                ->copyable(),

            TextColumn::make('saldo')
                ->label('Saldo')
                ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.'))
                ->color('success')
                ->sortable()
                ->alignRight(),

            TextColumn::make('bloqueado')
                ->label('Bloqueado')
                ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.'))
                ->color('danger')
                ->sortable()
                ->alignRight(),
        ];
    }

    public function getColumnSpan(): string|int
    {
        return 'full';
    }
}
