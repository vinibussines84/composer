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

            $model->user_id = $user->id;
            $model->status = 'pending';

            // Conversão segura para centavos
            $valorInformado = $model->amount;

            if (is_float($valorInformado) || str_contains((string) $valorInformado, '.')) {
                $valorEmCentavos = (int) round(((float) $valorInformado) * 100);
            } else {
                $valorEmCentavos = (int) $valorInformado;
            }

            $taxaPercentual = $user->taxa_cash_out ?? 0;
            $valorTaxa = (int) round($valorEmCentavos * ($taxaPercentual / 100));

            $valorTotal = $valorEmCentavos + $valorTaxa;

            // Verifica se há saldo suficiente para valor + taxa
            if ($valorTotal > ($user->saldo - $user->bloqueado)) {
                throw new \Exception('Saldo insuficiente para realizar o saque (com taxa).');
            }

            $model->amount = $valorEmCentavos;

            // Debita o total (valor + taxa) do saldo do usuário
            $user->decrement('saldo', $valorTotal);
        });

        static::created(function ($withdraw) {
            $user = $withdraw->user;

            PixTransaction::create([
                'user_id' => $withdraw->user_id,
                'authkey' => $user->authkey ?? '',
                'gtkey' => $user->gtkey ?? '',
                'external_transaction_id' => 'saque-' . $withdraw->id,
                'amount' => -$withdraw->amount, // apenas o valor bruto, sem taxa
                'balance_type' => 0,
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
