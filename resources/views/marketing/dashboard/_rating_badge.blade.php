@php
    $ratingConfig = [
        4 => ['class' => 'badge-success', 'icon' => 'mdi-emoticon-excited', 'label' => 'Muy Feliz'],
        3 => ['class' => 'badge-info', 'icon' => 'mdi-emoticon-happy', 'label' => 'Feliz'],
        2 => ['class' => 'badge-warning', 'icon' => 'mdi-emoticon-neutral', 'label' => 'Insatisfecho'],
        1 => ['class' => 'badge-danger', 'icon' => 'mdi-emoticon-sad', 'label' => 'Muy Insatisfecho'],
    ];
    $cfg = $ratingConfig[$rating] ?? ['class' => 'badge-secondary', 'icon' => 'mdi-help', 'label' => 'N/A'];
@endphp
<span class="badge {{ $cfg['class'] }}">
    <i class="mdi {{ $cfg['icon'] }}"></i> {{ $cfg['label'] }}
</span>
