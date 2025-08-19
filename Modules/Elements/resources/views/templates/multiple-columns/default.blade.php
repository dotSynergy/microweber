@php
    $cols = 3;
@endphp

<div class="mw-row nodrop" style="margin-block:0;padding: 15px 0;">
    @for($i = 1; $i <= $cols; $i++)
        <div class="mw-col cloneable allow-select" style="width: {{ 100/$cols }}%">
            <div class="mw-col-container safe-mode element no-select">
                <div class="mw-empty-element element allow-drop allow-edit allow-select"></div>
            </div>
        </div>
    @endfor
</div>
