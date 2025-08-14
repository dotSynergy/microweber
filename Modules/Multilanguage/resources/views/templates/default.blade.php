@php
/*
type: layout
name: Default
description: Default language switcher template
*/
@endphp

<div class="dropdown module-multilanguage lang-dropdown">
    <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center justify-content-center lang-flag-btn"
            type="button"
            id="dropdownMenuButton"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            style="background: #fff; border-radius: 50px; padding: 6px 10px; min-width: 44px;">
        @if($current_language['display_icon'])
            <img src="{{ $current_language['display_icon'] }}" alt="{{ $current_language['display_name'] }}" class="lang-flag">
        @else
            <span class="mw-flag-icon mw-flag-icon-{{ get_flag_icon($current_language['locale']) }} lang-flag"></span>
        @endif
    </button>

    <div class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="dropdownMenuButton" style="min-width: 56px;">
        @if(!empty($supported_languages))
            @foreach($supported_languages as $language)
                @if($language['is_active'])
                    <a class="dropdown-item d-flex align-items-center justify-content-center lang-flag-link p-1 @if($language['locale'] == $current_language['locale']) active @endif"
                       href="?lang={{ $language['locale'] }}"
                       title="{{ $language['display_name'] ?: $language['language'] }}">
                        @if($language['display_icon'])
                            <img src="{{ $language['display_icon'] }}" alt="{{ $language['display_name'] }}" class="lang-flag">
                        @else
                            <span class="mw-flag-icon mw-flag-icon-{{ get_flag_icon($language['locale']) }} lang-flag"></span>
                        @endif
                    </a>
                @endif
            @endforeach
        @endif
    </div>
</div>

<style>
.lang-dropdown .lang-flag-btn {
    background: none;
    border: none;
    outline: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.lang-flag {
    width: 32px;
    height: 24px;
    border-radius: 6px;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0,0,0,0.10);
    background-color: #eee;
    display: block;
    transition: box-shadow 0.18s, transform 0.18s;
}
.lang-flag-link {
    padding: 0;
    transition: background 0.15s, box-shadow 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
}
.lang-flag-link.active {
    background-color: #e3f2fd;
    box-shadow: 0 4px 16px rgba(25, 118, 210, 0.10);
}
.lang-flag-link:hover,
.lang-flag-link:focus {
    background-color: #f0f4fa;
    box-shadow: 0 4px 16px rgba(25, 118, 210, 0.08);
}
.dropdown-menu .lang-flag {
    width: 28px;
    height: 20px;
    border-radius: 5px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}
@media (max-width: 400px) {
    .lang-dropdown .lang-flag-btn {
        max-width: 44px;
        padding: 4px 2px;
    }
    .lang-flag {
        width: 26px;
        height: 18px;
    }
    .dropdown-menu .lang-flag {
        width: 22px;
        height: 16px;
    }
}
</style>

<script>
    mw.lib.require('flag_icons')
</script>
