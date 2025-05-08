<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Filament\Facades\Filament;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 👇 Força HTTPS em produção
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Filament::serving(function () {
            $panel = Filament::getCurrentPanel();

            if ($panel && $panel->getId() === 'admin_panel') {
                $panel->auth()->checkAccessUsing(function (User $user) {
                    return true; // ✅ Permite qualquer usuário autenticado
                });
            }
        });
    }
}
