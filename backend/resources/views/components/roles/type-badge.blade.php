@props(['isSystem' => false])

<span class="vestra-roles__badge {{ $isSystem ? 'vestra-roles__badge--info' : 'vestra-roles__badge--success' }}">
    {{ $isSystem ? 'System' : 'Custom' }}
</span>
