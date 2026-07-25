<x-filament-panels::page class="vestra-reports-page">
    <div class="reports-page space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $this->getTitle() }}</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Monitor distributor credit limits, utilization, and recent transactions.
            </p>
        </div>

        <x-filament::section icon="heroicon-o-funnel" heading="Filters">
            {{ $this->form }}
        </x-filament::section>

        @php
            $totalLimit = $this->getTotalCreditLimit();
            $totalOutstanding = $this->getTotalOutstanding();
            $totalAvailable = $this->getTotalAvailable();
            $accounts = $this->getCreditAccounts();
            $transactions = $this->getRecentTransactions();
        @endphp

        <section aria-labelledby="credit-kpis-heading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <h2 id="credit-kpis-heading" class="sr-only">Credit KPIs</h2>
            <x-reports.report-kpi-card label="Total Credit Limit" :value="'UGX ' . number_format($totalLimit)" icon="heroicon-o-credit-card" color="primary" />
            <x-reports.report-kpi-card label="Outstanding Balance" :value="'UGX ' . number_format($totalOutstanding)" icon="heroicon-o-banknotes" color="warning" />
            <x-reports.report-kpi-card label="Available Credit" :value="'UGX ' . number_format($totalAvailable)" icon="heroicon-o-check-circle" color="success" />
        </section>

        <section aria-labelledby="credit-accounts-heading">
            <h2 id="credit-accounts-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Credit Accounts</h2>
            <x-reports.report-table
                :columns="[['name' => 'distributor', 'label' => 'Distributor'], ['name' => 'credit_limit', 'label' => 'Limit'], ['name' => 'outstanding_balance', 'label' => 'Outstanding'], ['name' => 'available_credit', 'label' => 'Available'], ['name' => 'status', 'label' => 'Status']]"
                :rows="$accounts"
                emptyHeading="No credit accounts found"
            />
        </section>

        <section aria-labelledby="credit-transactions-heading">
            <h2 id="credit-transactions-heading" class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Recent Transactions</h2>
            <x-reports.report-table
                :columns="[['name' => 'distributor', 'label' => 'Distributor'], ['name' => 'type', 'label' => 'Type'], ['name' => 'amount', 'label' => 'Amount'], ['name' => 'created_at', 'label' => 'Date']]"
                :rows="$transactions"
                emptyHeading="No recent transactions"
            />
        </section>
    </div>
</x-filament-panels::page>
