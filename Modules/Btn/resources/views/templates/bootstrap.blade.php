@php
/*

type: layout

name: Bootstrap

description: Bootstrap button

*/
@endphp



@include('modules.components::module-data')



@include('modules.btn::components.custom-css')


@php
    $hasIcon = !empty($icon);
    $iconHtml = $hasIcon ? "<i class=\"{$icon}\"></i>" : '';
    $iconPosition = $iconPosition ?? 'left';
@endphp




@if($action == 'submit')
<button type="submit" id="{{ $btnId }}" class="btn {{ $style . ' ' . $size . ' ' . $class}}" {!! $attributes !!}>
    @if($hasIcon && $iconPosition == 'left'){!! $iconHtml !!}@endif
    {{ $text }}
    @if($hasIcon && $iconPosition == 'right'){!! $iconHtml !!}@endif
</button>
@elseif($action == 'popup')
    @include('modules.btn::components.popup')
    <a id="{{ $btnId }}" href="javascript:{{ $popupFunctionId }}()" class="btn {{ $style . ' ' . $size . ' ' . $class}}" {!! $attributes !!}>
        @if($hasIcon && $iconPosition == 'left'){!! $iconHtml !!}@endif
        {{ $text }}
        @if($hasIcon && $iconPosition == 'right'){!! $iconHtml !!}@endif
    </a>
@else
<a id="{{ $btnId }}" href="{{ $url }}" @if ($blank) target="_blank" @endif class="btn {{ $style . ' ' . $size . ' ' . $class}}" {!! $attributes !!}>
    @if($hasIcon && $iconPosition == 'left'){!! $iconHtml !!}@endif
    {{ $text }}
    @if($hasIcon && $iconPosition == 'right'){!! $iconHtml !!}@endif
</a>
@endif
