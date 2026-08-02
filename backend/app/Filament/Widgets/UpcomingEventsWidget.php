<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class UpcomingEventsWidget extends Widget
{
    protected static string $view = 'filament.widgets.upcoming-events';

    protected static bool $isLazy = true;

    protected int | string | array $columnSpan = ['lg' => 1];
}
