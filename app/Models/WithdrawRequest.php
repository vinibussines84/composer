<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\PixTransaction;

class WithdrawRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'pix_type',
        'pix_key',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $user = auth()->user();

            // Atribui ID do usuário e status padrão
            $model->user_id = $user->id;
            $model->status = 'pending';

            // Converte para centavos (caso usuário mande em reais)
            $valor = (float) $model->amount;

            // Se estiver entre R$0,01 e R$1000,00 (em centavos), converte
            if ($valor > 0 && $valor < 100000) {
                $valorEmCentavos = (int) round($valor * 100);
            } else {
                $valorEmCentavos = (int) $valor;
            }

            $model->amount = $valorEmCentavos;

            // Verifica se há saldo suficiente
            if ($valorEmCentavos > ($user->saldo - $user->bloqueado)) {
                throw new \Exception('Saldo insuficiente para realizar o saque.');
            }

            // Debita o saldo do usuário
            $user->decrement('saldo', $valorEmCentavos);
        });

        static::created(function ($withdraw) {
            $user = $withdraw->user;

            PixTransaction::create([
                'user_id' => $withdraw->user_id,
                'authkey' => $user->authkey ?? '',
                'gtkey' => $user->gtkey ?? '',
                'external_transaction_id' => 'saque-' . $withdraw->id,
                'amount' => -$withdraw->amount, // negativo pois é saída
                'balance_type' => 0, // saída
                'status' => $withdraw->status ?? 'pending',
                'created_at_api' => now(),
            ]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getValorLiquidoAttribute(): float
    {
        $taxa = $this->user?->taxa_cash_out ?? 0;
        $bruto = $this->amount / 100;
        return round($bruto - ($bruto * ($taxa / 100)), 2);
    }
}
