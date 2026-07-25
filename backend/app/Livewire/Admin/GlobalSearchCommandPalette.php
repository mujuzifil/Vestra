<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class GlobalSearchCommandPalette extends Component
{
    public bool $isOpen = false;

    public string $query = '';

    public bool $isLoading = false;

    public array $results = [];

    protected $listeners = ['open-command-palette' => 'open'];

    public function open(): void
    {
        $this->isOpen = true;
        $this->query = '';
        $this->results = [];
        $this->dispatch('command-palette-opened');
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query = '';
        $this->results = [];
    }

    public function updatedQuery(): void
    {
        $this->isLoading = true;

        // Placeholder: simulate search delay. Backend search logic will be wired in a future stage.
        $this->results = $this->performPlaceholderSearch($this->query);

        $this->isLoading = false;
    }

    private function performPlaceholderSearch(string $query): array
    {
        $demoResults = [
            'Orders' => [
                ['title' => 'Order #10042', 'subtitle' => 'Clarissa Mraz — $106.00', 'url' => route('filament.admin.resources.orders.edit', ['record' => 1]), 'icon' => 'heroicon-o-shopping-cart'],
                ['title' => 'Order #10041', 'subtitle' => 'Terence O\'Kon — $62.00', 'url' => route('filament.admin.resources.orders.edit', ['record' => 2]), 'icon' => 'heroicon-o-shopping-cart'],
            ],
            'Products' => [
                ['title' => 'EcoSuit Cleaner', 'subtitle' => 'SKU: ESC-001', 'url' => route('filament.admin.resources.products.edit', ['record' => 1]), 'icon' => 'heroicon-o-shopping-bag'],
                ['title' => 'Heavy Duty Detergent', 'subtitle' => 'SKU: HDD-002', 'url' => route('filament.admin.resources.products.edit', ['record' => 2]), 'icon' => 'heroicon-o-shopping-bag'],
            ],
            'Customers' => [
                ['title' => 'Clarissa Mraz', 'subtitle' => 'clarissa@example.com', 'url' => route('filament.admin.resources.customers.edit', ['record' => 1]), 'icon' => 'heroicon-o-users'],
            ],
        ];

        if (blank($query)) {
            return $demoResults;
        }

        $filtered = [];
        $lowerQuery = strtolower($query);

        foreach ($demoResults as $group => $items) {
            $groupItems = array_filter($items, function (array $item) use ($lowerQuery): bool {
                return str_contains(strtolower($item['title']), $lowerQuery)
                    || str_contains(strtolower($item['subtitle']), $lowerQuery);
            });

            if (! empty($groupItems)) {
                $filtered[$group] = array_slice(array_values($groupItems), 0, 5);
            }
        }

        return $filtered;
    }

    public function render()
    {
        return view('livewire.admin.global-search-command-palette');
    }
}
