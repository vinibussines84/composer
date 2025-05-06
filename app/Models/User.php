<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Filament\Panel;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'dashboard_access',
        'taxa_cash_in',
        'taxa_cash_out',
        'authkey',
        'gtkey',
        'senha',
        'cashin_ativo',
        'cashout_ativo',
        'webhookcashin',
        'webhookcashout',
        'saldo',
        'bloqueado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'cashin_ativo' => 'boolean',
        'cashout_ativo' => 'boolean',
        'saldo' => 'decimal:2',
        'bloqueado' => 'decimal:2',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
