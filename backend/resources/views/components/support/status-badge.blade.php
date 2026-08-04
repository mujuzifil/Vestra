@props(['status' => null])

@php
$map = [
    'open'        => ['label' => 'Open',        'class' => 'vestra-support__badge--open'],
    'in_progress' => ['label' => 'In Progress', 'class' => 'vestra-support__badge--in-progress'],
    'resolved'    => ['label' => 'Resolved',    'class' => 'vestra-support__badge--resolved'],
    'closed'      => ['label' => 'Closed',      'class' => 'vestra-support__badge--closed'],
];
$info = $map[$status] ?? ['label' => ucfirst($status ?? '—'), 'class' => ''];
@endphp

<span class="vestra-support__badge {{ $info['class'] }}">{{ $info['label'] }}</span>
