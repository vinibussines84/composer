<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BloobankWebhook extends Model
{
    protected $fillable = [
        'payload',
        'status',
    ];
}
