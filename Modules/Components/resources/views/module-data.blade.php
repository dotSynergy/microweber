@if(isset($__data) and !empty($__data) and is_live_edit() and isset($params) and isset($params['id']))

<script type="application/json" id="module-data--{{ $params['id'] }}" data-module-settings-id="{{ $params['id'] }}">
    {!! json_encode($__data); !!}
</script>

@endif
