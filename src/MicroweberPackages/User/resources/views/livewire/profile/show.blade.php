<div>
    <div class="mb-4">
        <h2 class="h4 font-weight-bold">
            {{ __('Profile') }}
        </h2>
    </div>

    <div>
        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            @livewire('profile.update-profile-information-form')

            <x-user::section-border />
        @endif

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            @livewire('profile.update-password-form')

            <x-user::section-border />
        @endif

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            @livewire('user::profile.two-factor-authentication-form')

            <x-user::section-border />
        @endif

        @livewire('user::profile.logout-other-browser-sessions-form')

        @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
            <x-user::section-border />

            @livewire('profile.delete-user-form')
        @endif
    </div>
</div>
