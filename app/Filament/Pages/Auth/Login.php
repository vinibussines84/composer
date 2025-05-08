<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Afatmustafa\FilamentTurnstile\Forms\Components\Turnstile;

class Login extends BaseLogin
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
                Turnstile::make('turnstile')
                    ->theme('dark')       // Pode usar 'dark' se preferir
                    ->size('normal')       // Pode usar 'compact'
                    ->language('pt-br'),   // Idioma em português do Brasil
            ])
            ->statePath('data');
    }
}
