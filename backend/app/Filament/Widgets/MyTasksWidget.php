<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class MyTasksWidget extends Widget
{
    protected static string $view = 'filament.widgets.my-tasks';

    protected static bool $isLazy = true;

    protected int | string | array $columnSpan = ['lg' => 1];
}
