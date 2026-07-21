<x-filament-panels::page class="vestra-administration-page">
    <div class="administration-health-page space-y-8">
        {{-- Header --}}
        <div>
            <p class="text-sm text-neutral-600">
                Monitor database, cache, queue, storage, mail, and scheduler status.
            </p>
        </div>

        {{-- Health checks --}}
        <section aria-labelledby="health-checks-heading">
            <h2 id="health-checks-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">
                Health Checks
            </h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->getHealthChecks() as $check)
                    <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="rounded-lg bg-{{ $this->getStatusColor($check['status']) }}-50 p-3">
                                <x-filament::icon :icon="$check['icon']" class="h-6 w-6 text-{{ $this->getStatusColor($check['status']) }}-600" />
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-neutral-900">{{ $check['name'] }}</h3>
                                    <span class="inline-flex items-center rounded-full bg-{{ $this->getStatusColor($check['status']) }}-50 px-2 py-1 text-xs font-medium text-{{ $this->getStatusColor($check['status']) }}-700">
                                        {{ ucfirst($check['status']) }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-neutral-600">{{ $check['message'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Environment info --}}
        <section aria-labelledby="environment-heading">
            <h2 id="environment-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">
                Environment
            </h2>
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm">
                <dl class="divide-y divide-neutral-100">
                    @foreach ($this->getEnvironmentInfo() as $label => $value)
                        <div class="flex flex-col py-3 sm:flex-row sm:justify-between sm:gap-4">
                            <dt class="text-sm font-medium text-neutral-600">{{ $label }}</dt>
                            <dd class="mt-1 text-sm text-neutral-900 sm:mt-0 sm:text-right">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>
    </div>
</x-filament-panels::page>
