@php
    use App\Enums\DistributorStatus;

    $application = $this->getRecord();
    $application->load('assignedAdministrator');

    $documents = $application->documents ?? [];
    $documentTypes = [
        'business_registration' => 'Business Registration',
        'tax_certificate' => 'Tax Certificate',
        'identification' => 'Identification',
        'licence' => 'Licence',
        'supporting' => 'Supporting Document',
    ];

    $timeline = [];

    $timeline[] = [
        'icon' => 'heroicon-o-truck',
        'color' => 'primary',
        'title' => 'Application submitted',
        'description' => 'Distributor application received from ' . $application->company_name . '.',
        'time' => $application->created_at,
        'actor' => $application->contact_person,
    ];

    if ($application->status !== DistributorStatus::PENDING) {
        $timeline[] = [
            'icon' => 'heroicon-o-shield-check',
            'color' => $application->statusColor(),
            'title' => 'Status changed to ' . $application->statusLabel(),
            'description' => 'Application status was updated.',
            'time' => $application->updated_at,
            'actor' => 'Administrator',
        ];
    }

    $auditLogs = \App\Models\AuditLog::query()
        ->where('subject_type', $application->getMorphClass())
        ->where('subject_id', $application->id)
        ->with('user')
        ->latest()
        ->limit(20)
        ->get();

    foreach ($auditLogs as $log) {
        $timeline[] = [
            'icon' => 'heroicon-o-pencil-square',
            'color' => 'gray',
            'title' => 'Application ' . str_replace(['.', '_'], ' ', $log->action),
            'description' => $log->details ? json_encode($log->details) : 'Application updated.',
            'time' => $log->created_at,
            'actor' => $log->user?->name ?? 'System',
        ];
    }

    usort($timeline, fn (array $a, array $b): int => ($a['time'] ?? now()) <=> ($b['time'] ?? now()));
@endphp

<x-filament-panels::page>
    <div class="fi-vestra-distributor-detail space-y-6">

        {{-- Application Summary --}}
        <x-filament::section icon="heroicon-o-building-office" heading="Application Summary">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-neutral-900">{{ $application->company_name }}</h2>
                    <p class="text-sm text-neutral-600">Submitted {{ $application->created_at?->diffForHumans() ?? '-' }}</p>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-2 sm:mt-0">
                    <x-filament::badge size="lg" :color="$application->statusColor()">
                        {{ $application->statusLabel() }}
                    </x-filament::badge>
                    <x-filament::badge size="lg" :color="$application->priorityColor()">
                        {{ $application->priorityLabel() }}
                    </x-filament::badge>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-neutral-50 p-3">
                    <p class="text-xs text-neutral-500">Business Type</p>
                    <p class="text-sm font-medium text-neutral-900">{{ $application->business_type ?? '—' }}</p>
                </div>
                <div class="rounded-lg bg-neutral-50 p-3">
                    <p class="text-xs text-neutral-500">Years in Operation</p>
                    <p class="text-sm font-medium text-neutral-900">{{ $application->years_in_operation ?? '—' }}</p>
                </div>
                <div class="rounded-lg bg-neutral-50 p-3">
                    <p class="text-xs text-neutral-500">Existing Customer</p>
                    <p class="text-sm font-medium text-neutral-900">{{ $application->isExistingCustomer() ? 'Yes' : 'No' }}</p>
                </div>
                <div class="rounded-lg bg-neutral-50 p-3">
                    <p class="text-xs text-neutral-500">Previous Applications</p>
                    <p class="text-sm font-medium text-neutral-900">{{ $application->previous_applications }}</p>
                </div>
            </div>
        </x-filament::section>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Business Information --}}
            <x-filament::section icon="heroicon-o-briefcase" heading="Business Information" class="lg:col-span-1">
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Business Name</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $application->company_name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Business Type</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $application->business_type ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Years in Operation</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $application->years_in_operation ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Estimated Volume</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $application->estimated_volume ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Existing Customer</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $application->isExistingCustomer() ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Previous Applications</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $application->previous_applications }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            {{-- Primary Contact --}}
            <x-filament::section icon="heroicon-o-user" heading="Primary Contact" class="lg:col-span-1">
                <div class="flex items-center gap-3">
                    <div class="fi-vestra-avatar h-12 w-12 text-sm">
                        {{ strtoupper(substr($application->contact_person, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-neutral-900">{{ $application->contact_person }}</p>
                        <p class="text-xs text-neutral-500">{{ $application->email }}</p>
                    </div>
                </div>
                <dl class="mt-4 space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Phone</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $application->phone ?? '—' }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            {{-- Business Address --}}
            <x-filament::section icon="heroicon-o-map-pin" heading="Business Address" class="lg:col-span-1">
                <dl class="space-y-3">
                    <div class="flex flex-col gap-1">
                        <dt class="text-sm text-neutral-500">Address</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $application->address ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Country</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $application->country ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-neutral-500">Region</dt>
                        <dd class="text-sm font-medium text-neutral-900">{{ $application->region ?? '—' }}</dd>
                    </div>
                </dl>
            </x-filament::section>
        </div>

        {{-- Business Details --}}
        <x-filament::section icon="heroicon-o-document-text" heading="Business Details">
            <div class="grid gap-6 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-medium text-neutral-700">Business Description</p>
                    <p class="mt-1 text-sm text-neutral-800">{{ $application->business_description ?? 'No description provided.' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-neutral-700">Products Interested In</p>
                    <p class="mt-1 text-sm text-neutral-800">{{ $application->products_interested_in ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-neutral-700">Target Region</p>
                    <p class="mt-1 text-sm text-neutral-800">{{ $application->target_region ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-neutral-700">Estimated Volume</p>
                    <p class="mt-1 text-sm text-neutral-800">{{ $application->estimated_volume ?? '—' }}</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Submitted Documents --}}
        <x-filament::section icon="heroicon-o-folder" heading="Submitted Documents">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($documentTypes as $key => $label)
                    @php
                        $document = $documents[$key] ?? null;
                    @endphp
                    <div class="rounded-lg border border-dashed {{ $document ? 'border-success-300 bg-success-50' : 'border-neutral-200 bg-neutral-50' }} p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2">
                                <x-filament::icon icon="heroicon-o-document-text" class="h-5 w-5 {{ $document ? 'text-success-600' : 'text-neutral-400' }}" />
                                <p class="text-sm font-medium {{ $document ? 'text-success-900' : 'text-neutral-600' }}">{{ $label }}</p>
                            </div>
                            @if ($document)
                                <x-filament::badge size="sm" color="success">Provided</x-filament::badge>
                            @else
                                <x-filament::badge size="sm" color="gray">Not Provided</x-filament::badge>
                            @endif
                        </div>
                        @if ($document && is_array($document))
                            <p class="mt-2 text-xs text-success-700">{{ $document['name'] ?? 'Document uploaded' }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        {{-- Review Decision --}}
        <x-filament::section icon="heroicon-o-shield-check" heading="Review Decision">
            <div class="grid gap-6 lg:grid-cols-3">
                <div>
                    <p class="text-sm text-neutral-500">Current Status</p>
                    <div class="mt-1">
                        <x-filament::badge size="lg" :color="$application->statusColor()">{{ $application->statusLabel() }}</x-filament::badge>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-neutral-500">Priority</p>
                    <div class="mt-1">
                        <x-filament::badge size="lg" :color="$application->priorityColor()">{{ $application->priorityLabel() }}</x-filament::badge>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-neutral-500">Assigned Administrator</p>
                    <p class="mt-1 text-sm font-medium text-neutral-900">{{ $application->assignedAdministrator?->name ?? 'Unassigned' }}</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Internal Notes --}}
        <x-filament::section icon="heroicon-o-document-text" heading="Internal Notes">
            @if ($application->internal_notes)
                <div class="rounded-lg bg-neutral-50 p-4">
                    <p class="text-sm text-neutral-800">{{ $application->internal_notes }}</p>
                </div>
            @else
                <div class="rounded-lg border border-dashed border-neutral-200 bg-neutral-50 p-6 text-center">
                    <x-filament::icon icon="heroicon-o-document-text" class="mx-auto h-8 w-8 text-neutral-300" />
                    <p class="mt-2 text-sm text-neutral-500">No internal notes. Add notes on the Edit page.</p>
                </div>
            @endif
        </x-filament::section>

        {{-- Activity Timeline --}}
        <x-filament::section icon="heroicon-o-clock" heading="Activity Timeline">
            <x-filament.vestra.vestra-timeline :events="$timeline" />
        </x-filament::section>

        {{-- Audit History --}}
        <x-filament::section icon="heroicon-o-shield-check" heading="Audit History">
            @if ($auditLogs->isNotEmpty())
                <ul class="divide-y divide-neutral-100">
                    @foreach ($auditLogs as $log)
                        <li class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-neutral-800">
                                    <span class="font-medium">{{ $log->user?->name ?? 'System' }}</span>
                                    <span class="text-neutral-500">{{ str_replace(['.', '_'], ' ', $log->action) }}</span>
                                </p>
                                @if ($log->details)
                                    <p class="text-xs text-neutral-500">{{ json_encode($log->details) }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-neutral-400">{{ $log->created_at?->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-neutral-500">No audit history available.</p>
            @endif
        </x-filament::section>

    </div>
</x-filament-panels::page>
