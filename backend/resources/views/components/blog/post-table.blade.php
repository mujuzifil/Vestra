@props([
    'posts' => null,
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
])

@php
$columns = [
    ['key' => 'title',    'label' => 'Article',   'sortable' => true,  'field' => 'title'],
    ['key' => 'author',   'label' => 'Author',    'sortable' => true,  'field' => 'author'],
    ['key' => 'category', 'label' => 'Categories','sortable' => false],
    ['key' => 'status',   'label' => 'Status',    'sortable' => true,  'field' => 'status'],
    ['key' => 'views',    'label' => 'Views',     'sortable' => true,  'field' => 'view_count'],
    ['key' => 'published','label' => 'Published', 'sortable' => true,  'field' => 'published_at'],
    ['key' => 'updated',  'label' => 'Updated',   'sortable' => true,  'field' => 'updated_at'],
    ['key' => 'actions',  'label' => '',          'sortable' => false],
];
@endphp

<div class="vestra-blog__table-wrap" role="region" aria-label="Blog articles" tabindex="0">
    <table class="vestra-blog__table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="vestra-blog__th vestra-blog__th--{{ $column['key'] }}">
                        @if ($column['sortable'])
                            <button
                                type="button"
                                wire:click="sortBy('{{ $column['field'] }}')"
                                class="vestra-blog__sort-btn"
                                aria-label="Sort by {{ $column['label'] }}"
                            >
                                <span>{{ $column['label'] }}</span>
                                <span class="vestra-blog__sort-icons">
                                    @if ($sortField === $column['field'])
                                        <x-filament::icon
                                            icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}"
                                            class="h-3.5 w-3.5"
                                        />
                                    @else
                                        <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 vestra-blog__sort-inactive" />
                                    @endif
                                </span>
                            </button>
                        @else
                            <span>{{ $column['label'] }}</span>
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($posts as $post)
                <x-blog.post-row :post="$post" />
            @endforeach
        </tbody>
    </table>
</div>
