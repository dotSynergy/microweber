@php
    if (isset($starSize) && $starSize) {
        $starSize = intval($starSize) . 'px!important;';
    }
    if (isset($starColor) && $starColor) {
        $starColor = $starColor . '!important;';
    }
    if (isset($starBgColor) && $starBgColor) {
        $starBgColor = $starBgColor . '!important;';
    }
@endphp

<style>
    #{{ $params['id'] ?? 'rating-stars' }} .starrr span {
        font-size: {{ $starSize ?? '24px' }};
        color: {{ $starColor ?? '#FFD700' }};
        background: {{ $starBgColor ?? 'transparent' }};
        border-radius: 50%;
        padding: 2px;
        display: inline-block;
    }
</style>

