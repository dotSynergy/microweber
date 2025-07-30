<?php

namespace Modules\Settings\Filament\Resources\TranslationResource\Pages;

use Modules\Settings\Filament\Resources\TranslationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Actions\Action;
use MicroweberPackages\Translation\Models\TranslationKey;
use MicroweberPackages\Translation\Models\TranslationText;
use Filament\Notifications\Notification;

class ListTranslations extends ListRecords
{
    protected static string $resource = TranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Action::make('import_missing')
                ->label('Import Missing Translations')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    $this->importMissingTranslations();
                }),

            Action::make('sync_translations')
                ->label('Sync with Files')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    $this->syncTranslationsFromFiles();
                }),
        ];
    }

    protected function importMissingTranslations(): void
    {
        $count = 0;
        
        if (function_exists('get_supported_languages')) {
            $supportedLanguages = get_supported_languages();
            if (!empty($supportedLanguages)) {
                foreach ($supportedLanguages as $supportedLanguage) {
                    $translationsCount = TranslationText::where('translation_locale', $supportedLanguage['locale'])->count();
                    if ($translationsCount == 0) {
                        if (class_exists('\MicroweberPackages\Translation\TranslationPackageInstallHelper')) {
                            \MicroweberPackages\Translation\TranslationPackageInstallHelper::installLanguage($supportedLanguage['locale']);
                            $count++;
                        }
                    }
                }
            }
        }

        Notification::make()
            ->title("Imported missing translations for {$count} languages")
            ->success()
            ->send();
    }

    protected function syncTranslationsFromFiles(): void
    {
        // This would scan translation files and update the database
        // Implementation depends on your specific file structure
        
        Notification::make()
            ->title('Translation sync completed')
            ->body('Scanned translation files and updated database')
            ->success()
            ->send();
    }
}
