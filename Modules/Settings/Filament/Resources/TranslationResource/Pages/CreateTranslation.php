<?php

namespace Modules\Settings\Filament\Resources\TranslationResource\Pages;

use Modules\Settings\Filament\Resources\TranslationResource;
use Filament\Resources\Pages\CreateRecord;
use MicroweberPackages\Translation\Models\TranslationText;

class CreateTranslation extends CreateRecord
{
    protected static string $resource = TranslationResource::class;

    protected function afterCreate(): void
    {
        // Create the initial translation if provided
        $data = $this->form->getState();
        
        if (!empty($data['initial_locale']) && !empty($data['initial_translation'])) {
            TranslationText::create([
                'translation_key_id' => $this->record->id,
                'translation_locale' => $data['initial_locale'],
                'translation_text' => $data['initial_translation'],
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
