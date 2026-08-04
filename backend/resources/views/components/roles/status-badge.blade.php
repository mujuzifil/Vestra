@props(['status' => null])

@php
$map = [
    'draft' => ['label' => 'Draft', 'class' => 'vestra-roles__badge--gray'],
    'published' => ['label' => 'Published', 'class' => 'vestra-roles__badge--success'],
    'scheduled' => ['label' => 'Scheduled', 'class' => 'vestra-roles__badge--warning'],
    'archived' => ['label' => 'Archived', 'class' => 'vestra-roles__badge--danger'],
];
$info = $map[$status] ?? ['label' => ucfirst(str_replace('_', ' ', $status ?? '—')), 'class' => 'vestra-roles__badge--gray'];
@endphp

<span class="vestra-roles__badge {{ $info['class'] }}">{{ $info['label'] }}</span>
