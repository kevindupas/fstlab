<?php

namespace App\Providers\Filament;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\ContactAdmin;
use App\Filament\Pages\ContactUser;
use App\Filament\Pages\Experiments\Sessions\ExperimentSessionExport;
use App\Filament\Pages\Experiments\Sessions\ExperimentSessions;
use App\Filament\Widgets\BannedUserWidget;
use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\ExperimentAccessRequestsWidget;
use App\Filament\Widgets\ExperimentTableWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Kenepa\ResourceLock\ResourceLockPlugin;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Illuminate\Support\Facades\Route;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

        $plugins = [
            FilamentApexChartsPlugin::make()
        ];

        /** @var User|null */
        $user = Auth::user();

        if ($user && $user->hasRole('supervisor')) {
            $plugins[] = FilamentSpatieRolesPermissionsPlugin::make();
            $plugins[] = ResourceLockPlugin::make();
        }


        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->registration(Register::class)
            ->passwordReset()
            ->profile()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->plugins($plugins)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                ExperimentSessions::class,
                ContactAdmin::class,
                ExperimentSessionExport::class,
                ContactUser::class,
            ])
            ->renderHook(
                'panels::global-search.after',
                fn(): string => Blade::render(<<<HTML
                <div class="flex items-center">
                    <a href="/" class="rounded-lg flex items-center gap-2 px-3 py-2 text-sm font-medium text-black dark:text-white hover:text-primary-500 focus:outline-none">
                        <x-heroicon-o-home class="w-5 h-5" />
                        Accueil
                    </a>
                </div>
            HTML)
            )
            ->userMenuItems([
                'profile' => MenuItem::make()->label('Edit profile'),
            ])
            ->routes(function () {
                Route::get('experiment-session-export/{record}', ExperimentSessionExport::class)
                    ->name('filament.admin.pages.experiment-session-export');
                Route::get('contact-user/{user}/{experiment?}', ContactUser::class)
                    ->name('filament.admin.pages.contact-user');
            })
            ->viteTheme('resources/css/filament/admin/theme.css')
            // ->viteTheme('resources/css/app.css')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                BannedUserWidget::class,
                ExperimentTableWidget::class,
                ExperimentAccessRequestsWidget::class,
                DashboardStatsWidget::class,
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
                \App\Http\Middleware\RedirectIfPendingApproval::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
