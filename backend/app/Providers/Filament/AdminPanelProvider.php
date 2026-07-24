<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Administration\AdministrationDashboard;
use App\Filament\Pages\Administration\SecurityPolicies;
use App\Filament\Pages\Administration\SystemHealth;
use App\Filament\Pages\ForcePasswordChange;
use App\Filament\Pages\Settings\SettingsDashboard;
use App\Filament\Pages\Settings\SystemInformation;
use App\Filament\Pages\Reports\CustomerReport;
use App\Filament\Pages\Reports\DistributorReport;
use App\Filament\Pages\Reports\EngagementReport;
use App\Filament\Pages\Reports\InventoryReport;
use App\Filament\Pages\Reports\ReportsDashboard;
use App\Filament\Pages\Reports\RevenueReport;
use App\Filament\Pages\Reports\SalesReport;
use App\Http\Middleware\EnsureAdminPasswordChanged;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->domain(config('app.admin_domain'))
            ->path('')
            ->login()
            ->brandName('VESTRA')
            ->brandLogo(fn () => view('filament.components.vestra-logo'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.svg'))
            ->font('Poppins')
            ->colors([
                'primary' => [
                    50 => '#f3f8fd',
                    100 => '#e6f1fb',
                    200 => '#b8d8f7',
                    300 => '#7db8ec',
                    400 => '#4a90d9',
                    500 => '#0d3b66',
                    600 => '#142c47',
                    700 => '#0d1f33',
                    800 => '#0a1628',
                    900 => '#050d18',
                    950 => '#020817',
                ],
                'danger' => [
                    50 => '#fef2f2',
                    100 => '#fee2e2',
                    200 => '#fecaca',
                    300 => '#fca5a5',
                    400 => '#f87171',
                    500 => '#dc2626',
                    600 => '#b91c1c',
                    700 => '#991b1b',
                    800 => '#7f1d1d',
                    900 => '#450a0a',
                    950 => '#450a0a',
                ],
                'success' => [
                    50 => '#f4fbf1',
                    100 => '#e8f5e4',
                    200 => '#d5f0c9',
                    300 => '#b3e6a0',
                    400 => '#8fd974',
                    500 => '#70c050',
                    600 => '#5aa33d',
                    700 => '#46822f',
                    800 => '#396729',
                    900 => '#1a3a15',
                    950 => '#0d260c',
                ],
                'warning' => [
                    50 => '#fdfbf2',
                    100 => '#fcf8e3',
                    200 => '#f5eac7',
                    300 => '#ecd99c',
                    400 => '#e0c66a',
                    500 => '#d4af37',
                    600 => '#b5952f',
                    700 => '#8f7526',
                    800 => '#6f5b20',
                    900 => '#3d3110',
                    950 => '#221b08',
                ],
                'info' => [
                    50 => '#eff6ff',
                    100 => '#dbeafe',
                    200 => '#bfdbfe',
                    300 => '#93c5fd',
                    400 => '#60a5fa',
                    500 => '#4a90d9',
                    600 => '#2563eb',
                    700 => '#1d4ed8',
                    800 => '#1e40af',
                    900 => '#1e3a8a',
                    950 => '#172554',
                ],
                'gray' => [
                    50 => '#f8fafc',
                    100 => '#f1f5f9',
                    200 => '#e2e8f0',
                    300 => '#cbd5e1',
                    400 => '#94a3b8',
                    500 => '#64748b',
                    600 => '#475569',
                    700 => '#334155',
                    800 => '#1e293b',
                    900 => '#0f172a',
                    950 => '#020617',
                ],
            ])
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationGroups([
                'E-Commerce',
                'Catalog',
                'Requests',
                'Reports',
                'Administration',
                'System',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                ForcePasswordChange::class,
                AdministrationDashboard::class,
                SecurityPolicies::class,
                SystemHealth::class,
                SettingsDashboard::class,
                SystemInformation::class,
                ReportsDashboard::class,
                RevenueReport::class,
                SalesReport::class,
                CustomerReport::class,
                InventoryReport::class,
                EngagementReport::class,
                DistributorReport::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
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
                EnsureAdminPasswordChanged::class,
            ]);
    }
}
