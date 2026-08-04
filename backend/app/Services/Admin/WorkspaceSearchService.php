<?php

namespace App\Services\Admin;

use App\Filament\Pages\Administration\RolesPage;
use App\Filament\Pages\Administration\StaffPage;
use App\Filament\Pages\CustomerSuccess\EnquiriesPage;
use App\Filament\Pages\CustomerSuccess\SupportPage;
use App\Filament\Pages\Distributors\ActivePartnersPage;
use App\Filament\Pages\Distributors\ApplicationsPage;
use App\Filament\Pages\Marketing\BlogPage;
use App\Filament\Pages\Marketing\MediaPage;
use App\Filament\Pages\Products\CategoriesPage;
use App\Filament\Pages\Products\ProductsPage;
use App\Filament\Pages\Sales\CompaniesPage;
use App\Filament\Pages\Sales\QuotesPage;
use App\Filament\Pages\Workspace\ActivityPage;
use App\Filament\Pages\Workspace\TasksPage;
use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\ContactMessage;
use App\Models\Distributor;
use App\Models\DistributorRequest;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\SupportTicket;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Throwable;

class WorkspaceSearchService
{
    /**
     * Search live CRM workspace entities. Never throws — failures are logged and skipped.
     *
     * @return array<string, array<int, array{title: string, subtitle: string, url: string, icon: string}>>
     */
    public function search(string $query, int $perGroup = 5): array
    {
        $term = trim($query);

        if ($term === '' || mb_strlen($term) < 2) {
            return [];
        }

        $results = [];

        foreach ($this->providers() as $group => $provider) {
            try {
                if (! ($provider['authorize'])()) {
                    continue;
                }

                $items = ($provider['search'])($term, $perGroup);

                if ($items !== []) {
                    $results[$group] = $items;
                }
            } catch (Throwable $e) {
                Log::warning('Workspace global search provider failed', [
                    'group' => $group,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * @return array<string, array{authorize: callable, search: callable}>
     */
    private function providers(): array
    {
        return [
            'Companies' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', CompanyProfile::class),
                'search' => function (string $term, int $limit): array {
                    return CompanyProfile::query()
                        ->search($term)
                        ->limit($limit)
                        ->get()
                        ->map(fn (CompanyProfile $company): array => [
                            'title' => $company->company_name ?: 'Untitled company',
                            'subtitle' => trim(($company->primary_contact_email ?? '').($company->status?->label() ? ' · '.$company->status->label() : '')),
                            'url' => CompaniesPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-building-office',
                        ])
                        ->all();
                },
            ],
            'Quotes' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', QuoteRequest::class),
                'search' => function (string $term, int $limit): array {
                    return QuoteRequest::query()
                        ->search($term)
                        ->limit($limit)
                        ->get()
                        ->map(fn (QuoteRequest $quote): array => [
                            'title' => $quote->reference_number ?? ('Quote #'.$quote->id),
                            'subtitle' => trim(($quote->company_name ?? $quote->full_name ?? '').' · '.($quote->status?->label() ?? '')),
                            'url' => QuotesPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-document-text',
                        ])
                        ->all();
                },
            ],
            'Applications' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', DistributorRequest::class),
                'search' => function (string $term, int $limit): array {
                    $like = '%'.$term.'%';

                    return DistributorRequest::query()
                        ->where(function ($q) use ($like): void {
                            $q->where('company_name', 'like', $like)
                                ->orWhere('contact_person', 'like', $like)
                                ->orWhere('email', 'like', $like);
                        })
                        ->limit($limit)
                        ->get()
                        ->map(fn (DistributorRequest $request): array => [
                            'title' => $request->company_name ?: 'Application',
                            'subtitle' => trim(($request->contact_person ?? '').' · '.($request->status?->label() ?? (string) $request->status)),
                            'url' => ApplicationsPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-clipboard-document-list',
                        ])
                        ->all();
                },
            ],
            'Partners' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', Distributor::class),
                'search' => function (string $term, int $limit): array {
                    $like = '%'.$term.'%';

                    return Distributor::query()
                        ->where(function ($q) use ($like): void {
                            $q->where('company_name', 'like', $like)
                                ->orWhere('trading_name', 'like', $like)
                                ->orWhere('email', 'like', $like);
                        })
                        ->limit($limit)
                        ->get()
                        ->map(fn (Distributor $partner): array => [
                            'title' => $partner->company_name ?: ($partner->trading_name ?: 'Partner'),
                            'subtitle' => $partner->email ?? '',
                            'url' => ActivePartnersPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-building-storefront',
                        ])
                        ->all();
                },
            ],
            'Products' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', Product::class),
                'search' => function (string $term, int $limit): array {
                    $like = '%'.$term.'%';

                    return Product::query()
                        ->where(function ($q) use ($like): void {
                            $q->where('name', 'like', $like)
                                ->orWhere('sku', 'like', $like)
                                ->orWhere('slug', 'like', $like);
                        })
                        ->limit($limit)
                        ->get()
                        ->map(fn (Product $product): array => [
                            'title' => $product->name,
                            'subtitle' => $product->sku ? 'SKU: '.$product->sku : ($product->slug ?? ''),
                            'url' => ProductsPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-cube',
                        ])
                        ->all();
                },
            ],
            'Categories' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', Category::class),
                'search' => function (string $term, int $limit): array {
                    $like = '%'.$term.'%';

                    return Category::query()
                        ->where(function ($q) use ($like): void {
                            $q->where('name', 'like', $like)
                                ->orWhere('slug', 'like', $like);
                        })
                        ->limit($limit)
                        ->get()
                        ->map(fn (Category $category): array => [
                            'title' => $category->name,
                            'subtitle' => $category->slug ?? '',
                            'url' => CategoriesPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-tag',
                        ])
                        ->all();
                },
            ],
            'Blog' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', BlogPost::class),
                'search' => function (string $term, int $limit): array {
                    $like = '%'.$term.'%';

                    return BlogPost::query()
                        ->where(function ($q) use ($like): void {
                            $q->where('title', 'like', $like)
                                ->orWhere('slug', 'like', $like);
                        })
                        ->limit($limit)
                        ->get()
                        ->map(fn (BlogPost $post): array => [
                            'title' => $post->title,
                            'subtitle' => $post->status?->label() ?? ($post->slug ?? ''),
                            'url' => BlogPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-newspaper',
                        ])
                        ->all();
                },
            ],
            'Media' => [
                'authorize' => fn (): bool => auth()->user()?->isAdmin() ?? false,
                'search' => function (string $term, int $limit): array {
                    $paginator = app(MediaAdminService::class)->paginate(
                        ['search' => $term],
                        'updated_at',
                        'desc',
                        $limit,
                        1
                    );

                    return collect($paginator->items())
                        ->take($limit)
                        ->map(function (array $item) use ($term): array {
                            return [
                                'title' => $item['name'] ?? $item['title'] ?? 'Media asset',
                                'subtitle' => $item['type'] ?? ($item['path'] ?? ''),
                                'url' => MediaPage::getUrl(['search' => $term]),
                                'icon' => 'heroicon-o-photo',
                            ];
                        })
                        ->values()
                        ->all();
                },
            ],
            'Staff' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', User::class),
                'search' => function (string $term, int $limit): array {
                    $like = '%'.$term.'%';

                    return User::query()
                        ->where('is_admin', true)
                        ->where(function ($q) use ($like): void {
                            $q->where('name', 'like', $like)
                                ->orWhere('email', 'like', $like);
                        })
                        ->limit($limit)
                        ->get()
                        ->map(fn (User $user): array => [
                            'title' => $user->name,
                            'subtitle' => $user->email,
                            'url' => StaffPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-users',
                        ])
                        ->all();
                },
            ],
            'Roles' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', Role::class),
                'search' => function (string $term, int $limit): array {
                    $like = '%'.$term.'%';

                    return Role::query()
                        ->where('name', 'like', $like)
                        ->limit($limit)
                        ->get()
                        ->map(fn (Role $role): array => [
                            'title' => $role->name,
                            'subtitle' => 'Role',
                            'url' => RolesPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-shield-check',
                        ])
                        ->all();
                },
            ],
            'Tasks' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', Task::class),
                'search' => function (string $term, int $limit): array {
                    return Task::query()
                        ->search($term)
                        ->limit($limit)
                        ->get()
                        ->map(fn (Task $task): array => [
                            'title' => $task->title,
                            'subtitle' => $task->status?->label() ?? '',
                            'url' => TasksPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-check-circle',
                        ])
                        ->all();
                },
            ],
            'Activities' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', AuditLog::class),
                'search' => function (string $term, int $limit): array {
                    $like = '%'.$term.'%';

                    return AuditLog::query()
                        ->where(function ($q) use ($like): void {
                            $q->where('action', 'like', $like)
                                ->orWhere('subject_type', 'like', $like);
                        })
                        ->latest()
                        ->limit($limit)
                        ->get()
                        ->map(fn (AuditLog $log): array => [
                            'title' => $log->action,
                            'subtitle' => class_basename((string) $log->subject_type).($log->subject_id ? ' #'.$log->subject_id : ''),
                            'url' => ActivityPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-clock',
                        ])
                        ->all();
                },
            ],
            'Support' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', SupportTicket::class),
                'search' => function (string $term, int $limit): array {
                    $like = '%'.$term.'%';

                    return SupportTicket::query()
                        ->where(function ($q) use ($like): void {
                            $q->where('subject', 'like', $like)
                                ->orWhere('reference_number', 'like', $like);
                        })
                        ->limit($limit)
                        ->get()
                        ->map(fn (SupportTicket $ticket): array => [
                            'title' => $ticket->subject ?: ('Ticket '.$ticket->reference_number),
                            'subtitle' => $ticket->reference_number ?? '',
                            'url' => SupportPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-lifebuoy',
                        ])
                        ->all();
                },
            ],
            'Enquiries' => [
                'authorize' => fn (): bool => Gate::allows('viewAny', ContactMessage::class),
                'search' => function (string $term, int $limit): array {
                    $like = '%'.$term.'%';

                    return ContactMessage::query()
                        ->where(function ($q) use ($like): void {
                            $q->where('name', 'like', $like)
                                ->orWhere('email', 'like', $like)
                                ->orWhere('subject', 'like', $like);
                        })
                        ->limit($limit)
                        ->get()
                        ->map(fn (ContactMessage $message): array => [
                            'title' => $message->subject ?: ($message->name ?: 'Enquiry'),
                            'subtitle' => $message->email ?? '',
                            'url' => EnquiriesPage::getUrl(['search' => $term]),
                            'icon' => 'heroicon-o-inbox',
                        ])
                        ->all();
                },
            ],
        ];
    }
}
