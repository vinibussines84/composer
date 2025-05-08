<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use App\Filament\Xota\Widgets\SaldoClientesWidget;
use Filament\Panel;
use App\Filament\Xota\Widgets\Ultimas10TransacoesResource; // sem acento
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Xota\Widgets\WithdrawRequestStatsWidget;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Filament\Xota\Widgets\Ultimas10TransaçõesResource;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class XotaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('xota')
            ->path('xota')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Xota/Resources'), for: 'App\\Filament\\Xota\\Resources')
            ->discoverPages(in: app_path('Filament/Xota/Pages'), for: 'App\\Filament\\Xota\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Xota/Widgets'), for: 'App\\Filament\\Xota\\Widgets')
            ->widgets([
                \App\Filament\Xota\Resources\Ultimas10TransacoesResource\Widgets\TransactionList::class,
                WithdrawRequestStatsWidget::class,
                SaldoClientesWidget::class,



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
                \Filament\Http\Middleware\Authenticate::class,
                'check.xota', // agora você pode usar só o alias
            ])
            
            ->maxContentWidth('ExtraLarge');
    }
}
