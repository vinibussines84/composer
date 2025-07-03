<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PluggouWebhook extends Model
{
    protected $fillable = ['payload', 'status'];
    protected $casts = [
        'payload' => 'array',
    ];
}
