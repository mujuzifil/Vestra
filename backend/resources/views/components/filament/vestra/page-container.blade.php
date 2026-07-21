@props([
    'fullWidth' => false,
])

<div {{
    $attributes->class([
        'mx-auto w-full',
        'max-w-[1440px]' => ! $fullWidth,
    ])
}}>
    {{ $slot }}
</div>
