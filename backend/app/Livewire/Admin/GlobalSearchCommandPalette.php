<?php

namespace App\Livewire\Admin;

use App\Services\Admin\WorkspaceSearchService;
use Livewire\Component;

class GlobalSearchCommandPalette extends Component
{
    public bool $isOpen = false;

    public string $query = '';

    public bool $isLoading = false;

    /** @var array<string, array<int, array{title: string, subtitle: string, url: string, icon: string}>> */
    public array $results = [];

    protected $listeners = ['open-command-palette' => 'open'];

    public function open(): void
    {
        $this->isOpen = true;
        $this->query = '';
        $this->results = [];
        $this->isLoading = false;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query = '';
        $this->results = [];
        $this->isLoading = false;
    }

    public function updatedQuery(string $value): void
    {
        $this->isLoading = true;

        try {
            $this->results = app(WorkspaceSearchService::class)->search($value);
        } catch (\Throwable $e) {
            report($e);
            $this->results = [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.global-search-command-palette');
    }
}
