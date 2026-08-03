@props(['cards' => []])

<div class="vestra-kpi-grid vestra-kpi-grid--5">
    @foreach ($cards as $card)
        <x-admin.kpi-card
            :icon="$card['icon']"
            :label="$card['title']"
            :value="$card['value']"
            :trend="null"
            :trend-available="false"
            :color="$card['color']"
        />
    @endforeach
</div>
