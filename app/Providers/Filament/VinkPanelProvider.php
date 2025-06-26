<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use App\Http\Middleware\CheckXotaAccess;
use App\Filament\Vink\Widgets\TotalPendentesWidget;

class VinkPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('vink')
            ->path('vink')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->authGuard('web') // mesmo guard do painel Xota
            ->login() // usa a rota de login padrão
            ->discoverResources(
                in: app_path('Filament/Vink/Resources'),
                for: 'App\\Filament\\Vink\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Vink/Pages'),
                for: 'App\\Filament\\Vink\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Vink/Widgets'),
                for: 'App\\Filament\\Vink\\Widgets'
            )
            ->widgets([
                TotalPendentesWidget::class, // ✅ Widget adicionado aqui
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                CheckXotaAccess::class,
            ]);
    }
}
