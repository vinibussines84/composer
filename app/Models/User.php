<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
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
        'dashrash',
        'is_central', // ✅ adicionado aqui
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
        'dashrash' => 'boolean',
        'is_central' => 'boolean', // ✅ adicionado aqui
        'saldo' => 'decimal:2',
        'bloqueado' => 'decimal:2',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'xota') {
            return $this->dashrash;
        }

        return true;
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->authkey = $user->authkey ?? strtoupper(Str::random(10));
            $user->gtkey = $user->gtkey ?? strtoupper(Str::random(10));
        });
    }
}
