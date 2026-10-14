<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\ContactSettings;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Blade;
use Prunacatalin\FilamentLocaleSwitcher\Http\Middleware\ApplyLocale;
use Prunacatalin\FilamentLocaleSwitcher\LocaleSwitchPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        FilamentAsset::register([
            Css::make('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'),
            Js::make('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'),
            // MapLibre is only needed for the dark theme's vector base layer and
            // is loaded lazily by map-base-layer.js. MapLibre's own CSS is
            // required for the canvas the bridge injects into the tile pane.
            Css::make('maplibre-gl', asset('vendor/maplibre/maplibre-gl.css')),
            Js::make('map-base-layer', asset('js/map-base-layer.js')),
        ], package: 'app');

        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_NAV_START,
            fn (): string => Blade::render('@livewire(\'App\Livewire\CompanySwitcher\')'),
        );
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path(env('FILAMENT_ADMIN_PATH', 'admin'))
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                \App\Filament\Widgets\AccountWidget::class,
                \App\Filament\Widgets\MapWidget::class,
                \App\Filament\Widgets\TopScannedItemsWidget::class,
                \App\Filament\Widgets\ItemScansChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ApplyLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                LocaleSwitchPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
            // NOTE: `strictAuthorization()` is deliberately NOT enabled.
            //
            // Strict mode requires every model to have a Gate policy method.
            // This app authorises through `can*()` overrides on the Resource
            // classes instead, so enabling it makes Filament throw a
            // LogicException for every resource that does not also declare a
            // policy (ActionRule, Item, Batch, Scan, ...), which 500s the
            // dashboard outright.
            //
            // The fail-open risk that strict mode would have caught is covered
            // by tests/Feature/AuthorizationCoverageTest.php instead: a missing
            // `can*()` override fails CI rather than silently allowing access.
    }
}
