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
use Filament\Support\Enums\MaxWidth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Jeffgreco13\FilamentBreezy\BreezyCore;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Awcodes\LightSwitch\Enums\Alignment;

// widgets finais
use App\Filament\Widgets\AdminStats;
use App\Filament\Widgets\Ultimas10TransacoesDoUsuario;
use App\Filament\Resources\RelatorioFinanceiroResource\Widgets\BlogPostsChart;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('trust')
            ->default()
            ->path('trust')

            // 👇 Redireciona para /login ao deslogar
            ->login(fn () => redirect()->route('login'))

            ->registration()
            ->passwordReset()
            ->profile()
            ->plugins([
                BreezyCore::make(),
                LightSwitchPlugin::make()->position(Alignment::BottomCenter),
            ])

            //lsk
            ->brandLogo(asset('theme/img/trustgate2.png'))
            ->brandLogoHeight('3rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AdminStats::class,
                Ultimas10TransacoesDoUsuario::class,
                BlogPostsChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->favicon('theme/img/favicon32.png')
            ->topNavigation()
            ->maxContentWidth(MaxWidth::Full);
    }
}
