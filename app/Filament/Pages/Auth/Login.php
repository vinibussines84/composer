<?php


namespace App\Filament\Pages\Auth;
 
use Filament\Forms\Form;
use Afatmustafa\FilamentTurnstile\Forms\Components\Turnstile;
 
class Login extends \Filament\Pages\Auth\Login
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
                Turnstile::make('turnstile')
                    ->theme('light')
                    ->size('normal')
                    ->language('en-US'),
            ])
            ->statePath('data');
    }
}