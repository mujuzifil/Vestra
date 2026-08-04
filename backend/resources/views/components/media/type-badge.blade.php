@props(['type' => 'other'])

@php
$map = [
    'image' => ['label' => 'Image', 'class' => 'vestra-media__badge--image', 'icon' => 'heroicon-o-photo'],
    'document' => ['label' => 'Document', 'class' => 'vestra-media__badge--document', 'icon' => 'heroicon-o-document-text'],
    'video' => ['label' => 'Video', 'class' => 'vestra-media__badge--video', 'icon' => 'heroicon-o-film'],
    'other' => ['label' => 'Other', 'class' => 'vestra-media__badge--other', 'icon' => 'heroicon-o-document'],
];
$info = $map[$type] ?? $map['other'];
@endphp

<span class="vestra-media__badge {{ $info['class'] }}">
    <x-filament::icon :icon="$info['icon']" class="h-3 w-3" />
    <span>{{ $info['label'] }}</span>
</span>
