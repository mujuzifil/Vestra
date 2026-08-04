@props(['visibility' => null])

@php
$map = [
    'public' => ['label' => 'Public', 'class' => 'vestra-blog__badge--success'],
    'internal' => ['label' => 'Internal', 'class' => 'vestra-blog__badge--warning'],
];
$info = $map[$visibility] ?? ['label' => ucfirst($visibility ?? '—'), 'class' => 'vestra-blog__badge--gray'];
@endphp

<span class="vestra-blog__badge {{ $info['class'] }}">{{ $info['label'] }}</span>
