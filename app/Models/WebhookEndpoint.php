<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WebhookEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'token',
    ];
}
