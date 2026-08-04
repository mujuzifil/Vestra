@props(['status' => null])

@php
$map = [
    'draft' => ['label' => 'Draft', 'class' => 'vestra-blog__badge--gray'],
    'published' => ['label' => 'Published', 'class' => 'vestra-blog__badge--success'],
    'scheduled' => ['label' => 'Scheduled', 'class' => 'vestra-blog__badge--warning'],
    'archived' => ['label' => 'Archived', 'class' => 'vestra-blog__badge--danger'],
];
$info = $map[$status] ?? ['label' => ucfirst(str_replace('_', ' ', $status ?? '—')), 'class' => 'vestra-blog__badge--gray'];
@endphp

<span class="vestra-blog__badge {{ $info['class'] }}">{{ $info['label'] }}</span>
