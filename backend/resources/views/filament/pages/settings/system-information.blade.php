<x-filament-panels::page class="vestra-settings-page">
    <div class="settings-system-page space-y-8">
        {{-- Header --}}
        <div>
            <p class="text-sm text-neutral-600">
                Read-only overview of the application environment, framework, and infrastructure configuration.
            </p>
        </div>

        {{-- Info grid --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @foreach ($this->getSections() as $section)
                <section
                    aria-labelledby="system-section-{{ $loop->index }}"
                    class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-primary-50 p-2">
                            <x-filament::icon :icon="$section['icon']" class="h-5 w-5 text-primary-600" />
                        </div>
                        <h2
                            id="system-section-{{ $loop->index }}"
                            class="text-sm font-semibold uppercase tracking-wider text-neutral-500"
                        >
                            {{ $section['heading'] }}
                        </h2>
                    </div>

                    <dl class="mt-4 divide-y divide-neutral-100">
                        @foreach ($section['items'] as $label => $value)
                            <div class="flex flex-col py-3 sm:flex-row sm:justify-between sm:gap-4">
                                <dt class="text-sm font-medium text-neutral-600">{{ $label }}</dt>
                                <dd class="mt-1 text-sm text-neutral-900 sm:mt-0 sm:text-right">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
