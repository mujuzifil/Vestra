<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-dashboard space-y-8">
        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Reports & Business Intelligence</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Unified analytics across revenue, sales, customers, inventory, engagement, and distributor operations.
            </p>
        </div>

        {{-- Overview KPIs --}}
        <section aria-labelledby="overview-kpis-heading">
            <h2 id="overview-kpis-heading" class="sr-only">Overview KPIs</h2>
            @livewire(\App\Filament\Widgets\Reports\ReportsOverviewKpiWidget::class)
        </section>

        {{-- Report Navigation --}}
        <section aria-labelledby="report-categories-heading">
            <h2 id="report-categories-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">
                Report Categories
            </h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->getReportLinks() as $link)
                    <a href="{{ $link['route'] }}" class="group block rounded-xl border border-neutral-200 bg-white p-5 shadow-sm transition-all hover:border-primary-300 hover:shadow-md">
                        <div class="flex items-start gap-4">
                            <div class="rounded-lg bg-{{ $link['color'] }}-50 p-3">
                                <x-filament::icon :icon="$link['icon']" class="h-6 w-6 text-{{ $link['color'] }}-600" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-neutral-900 group-hover:text-primary-600">{{ $link['label'] }}</h3>
                                <p class="mt-1 text-sm text-neutral-600">{{ $link['description'] }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</x-filament-panels::page>
