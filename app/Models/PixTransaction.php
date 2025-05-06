<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PixTransaction extends Model
{
    use HasFactory;

    protected $table = 'pix_transactions'; // explicitamente define a tabela

    protected $fillable = [
        'user_id',
        'authkey',
        'gtkey',
        'external_transaction_id',
        'amount',
        'status',
        'pix',
        'created_at_api',
    ];

    protected $casts = [
        'pix' => 'array',
        'created_at_api' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
