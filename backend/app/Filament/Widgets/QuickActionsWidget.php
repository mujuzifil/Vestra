<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';

    protected int | string | array $columnSpan = 'full';

    public function getActions(): array
    {
        return [
            [
                'label' => 'Create Product',
                'icon' => 'heroicon-m-plus-circle',
                'url' => route('filament.admin.resources.products.create'),
                'color' => 'primary',
            ],
            [
                'label' => 'Pending Orders',
                'icon' => 'heroicon-m-clock',
                'url' => route('filament.admin.resources.orders.index', ['tableFilters' => ['status' => ['value' => 'pending']]]),
                'color' => 'warning',
            ],
            [
                'label' => 'Reply to Messages',
                'icon' => 'heroicon-m-envelope',
                'url' => route('filament.admin.resources.contact-messages.index', ['tableFilters' => ['status' => ['value' => 'new']]]),
                'color' => 'info',
            ],
            [
                'label' => 'Manage Settings',
                'icon' => 'heroicon-m-cog-6-tooth',
                'url' => route('filament.admin.resources.settings.index'),
                'color' => 'gray',
            ],
            [
                'label' => 'Reports',
                'icon' => 'heroicon-m-chart-pie',
                'url' => '#',
                'color' => 'gray',
                'disabled' => true,
            ],
        ];
    }
}
