@if(isset($__data) and !empty($__data) and is_live_edit() and isset($params) and isset($params['id']))

<script type="application/json" id="module-data-{{ $params['id'] }}" data-module-settins-id="{{ $params['id'] }}">
    {!! json_encode(array_filter($__data)); !!}
</script>

@endif
