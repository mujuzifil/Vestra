@props(['bytes' => null])

@php
if ($bytes === null) {
    $label = '—';
} elseif ($bytes >= 1_073_741_824) {
    $label = number_format($bytes / 1_073_741_824, 2).' GB';
} elseif ($bytes >= 1_048_576) {
    $label = number_format($bytes / 1_048_576, 2).' MB';
} elseif ($bytes >= 1024) {
    $label = number_format($bytes / 1024, 2).' KB';
} else {
    $label = $bytes.' B';
}
@endphp

{{ $label }}
