<?php

namespace App\Filament\Widgets;

use App\Models\UnifiedTransaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Ultimas10TransacoesDoUsuario extends BaseWidget
{
    public static ?string $heading = '🔁 Últimas 10 Transações';
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '10s';

    public function table(Table $table): Table
    {
        $userId = Auth::id();

        // Criar subquery com bindings via DB::select para evitar injeção e falhas
        $rawSql = "
            (
                SELECT 
                    pt.id,
                    pt.user_id,
                    u.name AS user_name,
                    pt.external_transaction_id,
                    pt.balance_type,
                    pt.amount,
                    CASE 
                        WHEN pt.balance_type = 1 THEN u.taxa_cash_in 
                        ELSE u.taxa_cash_out 
                    END AS taxa,
                    pt.status,
                    pt.created_at AS created_at_api,
                    'pix' AS origem
                FROM pix_transactions pt
                JOIN users u ON u.id = pt.user_id
                WHERE pt.user_id = ? 
                  AND (pt.balance_type != 1 OR (pt.balance_type = 1 AND pt.status = 'paid'))

                UNION ALL

                SELECT 
                    wr.id,
                    wr.user_id,
                    u.name AS user_name,
                    CONCAT('SAQUE-', wr.id),
                    0 AS balance_type,
                    wr.amount * -1,
                    u.taxa_cash_out AS taxa,
                    wr.status,
                    wr.created_at AS created_at_api,
                    'withdraw' AS origem
                FROM withdraw_requests wr
                JOIN users u ON u.id = wr.user_id
                WHERE wr.user_id = ?
            ) as unified_transactions
        ";

        // Encapsula com DB::table()->select() para aplicar bindings de forma limpa
        return $table
            ->poll(static::$pollingInterval)
            ->paginated(false)
            ->query(
                DB::table(DB::raw($rawSql))
                    ->select('*')
                    ->addBinding([$userId, $userId], 'select')
                    ->orderByDesc('created_at_api')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('external_transaction_id')->label('ID'),

                Tables\Columns\TextColumn::make('balance_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => $state == 1 ? 'Entrada' : 'Saída')
                    ->icon(fn ($state) => $state == 1 ? 'heroicon-m-arrow-up-circle' : 'heroicon-m-arrow-down-circle')
                    ->iconPosition('before')
                    ->color(fn ($state) => $state == 1 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format(abs($state) / 100, 2, ',', '.'))
                    ->color(fn ($state) => abs($state) > 10000 ? 'warning' : 'default')
                    ->icon(fn ($state) => abs($state) > 10000 ? 'heroicon-o-exclamation-circle' : null)
                    ->iconPosition('before'),

                Tables\Columns\TextColumn::make('taxa')
                    ->label('Taxa')
                    ->formatStateUsing(fn ($state, $record) => 
                        'R$ ' . number_format((abs($record->amount) / 100) * ($record->taxa / 100), 2, ',', '.')
                    ),

                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->icon(fn (string $state) => match (strtolower($state)) {
                        'paid', 'approved', 'autorizado' => 'heroicon-o-check-circle',
                        'pending', 'waiting_payment' => 'heroicon-o-clock',
                        'refused', 'cancelled', 'chargeback', 'cancelado' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state) => match (strtolower($state)) {
                        'paid', 'approved', 'autorizado' => 'success',
                        'pending', 'waiting_payment' => 'warning',
                        'refused', 'cancelled', 'chargeback', 'cancelado' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at_api')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }

    public function getColumnSpan(): string|int
    {
        return 'full';
    }
}
