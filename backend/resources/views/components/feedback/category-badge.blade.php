@props(['category' => null])

@php
use App\Enums\FeedbackCategory;

$enum = FeedbackCategory::tryFrom((string) $category);
$label = $enum?->label() ?? ucfirst((string) $category);

$colorMap = [
    'general' => 'gray',
    'bug' => 'danger',
    'feature' => 'info',
    'complaint' => 'warning',
    'praise' => 'success',
];
$color = $colorMap[$category] ?? 'gray';
@endphp

<span class="vestra-feedback__badge vestra-feedback__badge--{{ $color }}">{{ $label }}</span>
