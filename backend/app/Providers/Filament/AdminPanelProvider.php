<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ForcePasswordChange;
use App\Filament\Pages\Workspace\ActivityPage;
use App\Filament\Pages\Workspace\NotificationsPage;
use App\Filament\Pages\Workspace\TasksPage;
use App\Filament\Pages\Distributors\TerritoriesPage;
use App\Filament\Pages\Distributors\ApplicationsPage;
use App\Filament\Pages\Sales\CompaniesPage;
use App\Filament\Pages\Sales\QuotesPage;
use App\Filament\Pages\Sales\PipelinePage;
use App\Filament\Pages\Sales\OpportunitiesPage;
use App\Filament\Pages\Distributors\ActivePartnersPage;
use App\Filament\Pages\Distributors\CreditPage;
use App\Filament\Pages\CustomerSuccess\SupportPage;
use App\Filament\Pages\CustomerSuccess\EnquiriesPage;
use App\Filament\Pages\CustomerSuccess\FeedbackPage;
use App\Filament\Pages\Products\CategoriesPage;
use App\Filament\Pages\Marketing\MediaPage;
use App\Filament\Pages\Marketing\SeoPage;
use App\Filament\Pages\Analytics\ExecutiveAnalyticsPage;
use App\Filament\Pages\Analytics\SalesAnalyticsPage;
use App\Filament\Pages\Analytics\OperationsAnalyticsPage;
use App\Filament\Pages\Analytics\FinanceAnalyticsPage;
use App\Filament\Pages\Administration\IntegrationsPage;
use App\Http\Controllers\Admin\ActivityExportController;
use App\Http\Controllers\Admin\ApplicationExportController;
use App\Http\Controllers\Admin\CompanyExportController;
use App\Http\Controllers\Admin\CreditExportController;
use App\Http\Controllers\Admin\QuoteExportController;
use App\Http\Controllers\Admin\PartnerExportController;
use App\Http\Controllers\Admin\SupportExportController;
use App\Http\Controllers\Admin\EnquiryExportController;
use App\Http\Controllers\Admin\FeedbackExportController;
use App\Http\Controllers\Admin\CategoryExportController;
use App\Http\Controllers\Admin\TerritoryExportController;
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
use Illuminate\Support\Facades\Route;
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
            ->brandLogo(fn () => view('filament.components.vestra-logo', ['variant' => 'admin']))
            ->brandLogoHeight('2.5rem')
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
                'Workspace',
                'Sales',
                'Distributors',
                'Customer Success',
                'Products',
                'Operations',
                'Marketing',
                'Analytics',
                'Communications',
                'Administration',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                ForcePasswordChange::class,
                TasksPage::class,
                NotificationsPage::class,
                ActivityPage::class,
                CompaniesPage::class,
                QuotesPage::class,
                ApplicationsPage::class,
                TerritoriesPage::class,
                CreditPage::class,
                PipelinePage::class,
                OpportunitiesPage::class,
                ActivePartnersPage::class,
                SupportPage::class,
                EnquiriesPage::class,
                FeedbackPage::class,
                CategoriesPage::class,
                MediaPage::class,
                SeoPage::class,
                ExecutiveAnalyticsPage::class,
                SalesAnalyticsPage::class,
                OperationsAnalyticsPage::class,
                FinanceAnalyticsPage::class,
                IntegrationsPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->authenticatedRoutes(function (): void {
                Route::get('workspace/activity/export', ActivityExportController::class)
                    ->name('workspace.activity.export');
                Route::get('sales/companies/export', CompanyExportController::class)
                    ->name('sales.companies.export');
                Route::get('sales/quotes/export', QuoteExportController::class)
                    ->name('sales.quotes.export');
                Route::get('distributors/applications/export', ApplicationExportController::class)
                    ->name('distributors.applications.export');
                Route::get('distributors/active-partners/export', PartnerExportController::class)
                    ->name('distributors.active-partners.export');
                Route::get('distributors/territories/export', TerritoryExportController::class)
                    ->name('distributors.territories.export');
                Route::get('distributors/credit/export', CreditExportController::class)
                    ->name('distributors.credit.export');
                Route::get('customer-success/support/export', SupportExportController::class)
                    ->name('customer-success.support.export');
                Route::get('customer-success/enquiries/export', EnquiryExportController::class)
                    ->name('customer-success.enquiries.export');
                Route::get('customer-success/feedback/export', FeedbackExportController::class)
                    ->name('customer-success.feedback.export');
                Route::get('products/categories/export', CategoryExportController::class)
                    ->name('products.categories.export');
            })
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
