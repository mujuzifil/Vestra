@props(['cards' => []])

<div class="vestra-kpi-grid vestra-kpi-grid--6">
    @foreach ($cards as $card)
        <x-admin.kpi-card
            :icon="$card['icon']"
            :label="$card['label']"
            :value="$card['value']"
            :trend="$card['trend']"
            :trend-label="$card['trend_label']"
            :trend-positive="$card['trend_positive']"
            :trend-available="$card['trend_available']"
            :color="$card['color']"
        />
    @endforeach
</div>
