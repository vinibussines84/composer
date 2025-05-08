<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WithdrawRequestResource\Pages;
use App\Models\WithdrawRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class WithdrawRequestResource extends Resource
{
    protected static ?string $model = WithdrawRequest::class;

    protected static ?string $navigationLabel = 'Saques';
    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';

    public static function getModelLabel(): string
    {
        return 'Saque';
    }

    public static function getPluralModelLabel(): string
    {
        return '💸 Saques';
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $saldoDisponivel = ($user->saldo - $user->bloqueado) / 100;
        $taxa = $user->taxa_cash_out ?? 0;

        return $form
            ->schema([
                Placeholder::make('saldo_disponivel')
                    ->label('📈 Saldo disponível')
                    ->content(function () use ($saldoDisponivel) {
                        $cor = $saldoDisponivel > 0 ? 'green' : 'red';
                        $valorFormatado = 'R$ ' . number_format($saldoDisponivel, 2, ',', '.');
                        return new HtmlString("<span style='color: {$cor}; font-weight: bold;'>{$valorFormatado}</span>");
                    }),

                Select::make('pix_type')
                    ->label('Tipo da chave PIX')
                    ->required()
                    ->options([
                        'cpf' => 'CPF',
                        'cnpj' => 'CNPJ',
                        'email' => 'E-mail',
                        'telefone' => 'Telefone',
                        'aleatoria' => 'Chave Aleatória',
                    ]),

                TextInput::make('pix_key')
                    ->label('🔑 Chave PIX')
                    ->required(),

                TextInput::make('amount')
                    ->label('💰 Valor a sacar')
                    ->prefix('R$')
                    ->numeric()
                    ->reactive()
                    ->minValue(0.01)
                    ->required()
                    ->rule(function () use ($saldoDisponivel) {
                        return function (string $attribute, $value, $fail) use ($saldoDisponivel) {
                            if ($value > $saldoDisponivel) {
                                $fail("O valor solicitado excede o saldo disponível de R$ " . number_format($saldoDisponivel, 2, ',', '.'));
                            }
                        };
                    }),

                Placeholder::make('valor_recebido_liquido')
                    ->label("Você receberá")
                    ->content(function (callable $get) use ($taxa) {
                        $valor = $get('amount');
                        if (!$valor || $valor <= 0) return 'R$ 0,00';
                        $liquido = $valor - ($valor * ($taxa / 100));
                        return 'R$ ' . number_format($liquido, 2, ',', '.') . " Valor já descontado com a taxa de $taxa%";
                    }),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->getStateUsing(fn ($record) => $record->amount / 100)
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('pix_type')->label('Tipo de chave'),
                Tables\Columns\TextColumn::make('pix_key')->label('🔑 Chave PIX'),

                IconColumn::make('status')
                    ->label('Status')
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'autorizado' => 'heroicon-o-check-circle',
                        'cancelado' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'autorizado' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')->label('Data')->since(),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWithdrawRequests::route('/'),
            'create' => Pages\CreateWithdrawRequest::route('/create'), // ✅ rota de criação
        ];
    }
}
