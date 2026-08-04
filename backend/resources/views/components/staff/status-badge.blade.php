@props(['status' => null])

@php
$map = [
    'active' => ['label' => 'Active', 'class' => 'vestra-staff__badge--success'],
    'inactive' => ['label' => 'Inactive', 'class' => 'vestra-staff__badge--danger'],
];
$info = $map[$status] ?? ['label' => ucfirst(str_replace('_', ' ', $status ?? '—')), 'class' => 'vestra-staff__badge--gray'];
@endphp

<span class="vestra-staff__badge {{ $info['class'] }}">{{ $info['label'] }}</span>
