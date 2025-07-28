<div class="d-flex align-items-center justify-content-between mt-5 gap-3">
    <div class="form-group">
        @if(get_option('enable_captcha', $params['id']))
            <module type="captcha" id="captcha_contact_form-{{ $form_id }}"/>
        @endif
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary w-100" :disabled="$data.loading">
            <span x-show="!$data.loading">{{ $button_text }}</span>
            <span x-cloak x-show="$data.loading">@lang('Sending...')</span>
        </button>
    </div>
</div>
