@props(['priority' => null])

@php
$map = [
    'low'    => ['label' => 'Low',    'class' => 'vestra-support__badge--priority-low'],
    'medium' => ['label' => 'Medium', 'class' => 'vestra-support__badge--priority-medium'],
    'high'   => ['label' => 'High',   'class' => 'vestra-support__badge--priority-high'],
    'urgent' => ['label' => 'Urgent', 'class' => 'vestra-support__badge--priority-urgent'],
];
$info = $map[$priority] ?? ['label' => ucfirst($priority ?? '—'), 'class' => ''];
@endphp

<span class="vestra-support__badge {{ $info['class'] }}">{{ $info['label'] }}</span>
