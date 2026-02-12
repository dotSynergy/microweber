<div class="mw-filament-page">
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-4 justify-start">
            <button type="submit" class="btn btn-primary">
                Save
            </button>

            <a class="btn btn-outline-secondary" href="{{ $this->cancel_button_url }}">
                Cancel
            </a>
        </div>
    </form>
</div>
