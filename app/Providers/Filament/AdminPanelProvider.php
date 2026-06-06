<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => \Filament\Support\Colors\Color::Green,
            ])
            ->brandName('PFV Retail')
            ->brandLogo(fn () => new \Illuminate\Support\HtmlString('
                <div style="display: flex !important; align-items: center !important; gap: 10px !important; flex-direction: row !important; flex-wrap: nowrap !important;">
                    <img src="' . asset('logo.png') . '" alt="Logo" style="height: 2.5rem !important; width: auto !important; object-fit: contain !important; display: inline-block !important; margin: 0 !important; padding: 0 !important; flex-shrink: 0 !important;" />
                    <span style="font-size: 1.25rem !important; font-weight: 800 !important; color: #16a34a !important; letter-spacing: -0.025em !important; white-space: nowrap !important; display: inline-block !important; font-family: \'Outfit\', sans-serif !important;">PFV Retail</span>
                </div>
            '))
            ->font('Outfit')
            ->darkMode(false)
            ->assets([
                \Filament\Support\Assets\Css::make('custom-filament-theme', asset('css/custom-filament.css')),
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '
                    <link rel="manifest" href="' . asset('manifest.json') . '">
                    <meta name="theme-color" content="#16a34a">
                    <meta name="apple-mobile-web-app-capable" content="yes">
                    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
                    <link rel="apple-touch-icon" href="' . asset('logo.png') . '">
                    <link rel="stylesheet" href="' . asset('css/custom-filament.css') . '?v=' . time() . '">
                '
            )
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('POS Kasir')
                    ->url('/pos', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-shopping-cart')
                    ->sort(1),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\RedirectCashierToPos::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
