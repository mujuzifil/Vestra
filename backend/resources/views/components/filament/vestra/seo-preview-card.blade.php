@props([
    'slug' => '',
    'title' => '',
    'description' => '',
])

@php
    $titleLength = strlen($title);
    $descriptionLength = strlen($description);
@endphp

<div class="fi-vestra-seo-preview rounded-xl border border-neutral-200 bg-white p-5 shadow-sm">
    <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Search Engine Preview</p>

    <div class="mt-3 space-y-1">
        <p class="truncate text-sm text-neutral-800">
            {{ config('app.url') }}<span class="text-neutral-400">/</span><span class="text-neutral-600">{{ ltrim($slug, '/') }}</span>
        </p>
        <p class="cursor-pointer truncate text-lg font-medium text-primary-600 hover:underline">
            {{ $title ?: 'Page Title' }}
        </p>
        <p class="line-clamp-2 text-sm text-neutral-600">
            {{ $description ?: 'No meta description set. Add one to improve search visibility.' }}
        </p>
    </div>

    <div class="mt-4 flex flex-wrap gap-4 text-xs text-neutral-500">
        <span>
            Title: <span @class(['font-medium', 'text-danger-500' => $titleLength > 60, 'text-success-500' => $titleLength > 0 && $titleLength <= 60])">{{ $titleLength }}</span> / 60
        </span>
        <span>
            Description: <span @class(['font-medium', 'text-danger-500' => $descriptionLength > 160, 'text-success-500' => $descriptionLength > 0 && $descriptionLength <= 160])">{{ $descriptionLength }}</span> / 160
        </span>
    </div>
</div>
