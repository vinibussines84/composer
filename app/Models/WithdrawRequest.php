<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    protected $fillable = [
        'user_id',    // Permite atribuição em massa para user_id
        'amount',
        'pix_type',
        'pix_key',
        'status',
    ];

    // Relacionamento com o usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
