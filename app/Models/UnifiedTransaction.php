<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // ✅ Importa corretamente

class UnifiedTransaction extends Model
{
    public $timestamps = false;
    protected $table = 'unified_transactions'; // nome fictício
    protected $guarded = [];

    // Não existe tabela real, usamos apenas para consulta
    public function getTable()
    {
        return DB::raw("({$this->getQuerySql()}) as unified_transactions");
    }

    protected function getQuerySql(): string
    {
        $pix = DB::table('pix_transactions')
            ->selectRaw('
                id,
                user_id,
                external_transaction_id,
                balance_type,
                amount,
                CASE WHEN balance_type = 1 THEN users.taxa_cash_in ELSE users.taxa_cash_out END AS taxa,
                status,
                created_at AS created_at_api,
                "pix" AS origem
            ')
            ->join('users', 'users.id', '=', 'pix_transactions.user_id')
            ->where(function ($query) {
                $query->where('balance_type', '!=', 1)
                    ->orWhere(function ($q) {
                        $q->where('balance_type', 1)->where('status', 'paid');
                    });
            });

        $withdraw = DB::table('withdraw_requests')
            ->selectRaw('
                id,
                user_id,
                CONCAT("SAQUE-", id) AS external_transaction_id,
                0 AS balance_type,
                amount * -1 AS amount,
                users.taxa_cash_out AS taxa,
                status,
                created_at AS created_at_api,
                "withdraw" AS origem
            ')
            ->join('users', 'users.id', '=', 'withdraw_requests.user_id');

        return $pix->unionAll($withdraw)->toSql();
    }
}
