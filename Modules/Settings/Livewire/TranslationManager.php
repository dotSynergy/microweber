<?php

namespace Modules\Settings\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use MicroweberPackages\Translation\Models\TranslationKey;
use MicroweberPackages\Translation\Models\TranslationText;
use Illuminate\Support\Facades\Config;
use Filament\Notifications\Notification;

class TranslationManager extends Component
{
    use WithPagination;

    public $namespace = 'global';
    public $search = '';
    public $currentPage = 1;
    public $perPage = 10;
    
    public $supportedLanguages = [];
    public $translations = [];
    public $editingTranslations = [];

    protected $listeners = [
        'translationUpdated' => 'refreshTranslations',
        'searchUpdated' => 'updateSearch'
    ];

    public function mount($namespace = 'global')
    {
        $this->namespace = $namespace;
        $this->loadSupportedLanguages();
        $this->loadTranslations();
    }

    public function loadSupportedLanguages()
    {
        if (function_exists('get_supported_languages')) {
            $this->supportedLanguages = get_supported_languages(true);
        }

        // Add default language if no supported languages
        if (empty($this->supportedLanguages) && function_exists('mw')) {
            $currentLanguageAbr = mw()->lang_helper->default_lang();
            $this->supportedLanguages[] = [
                'icon' => get_flag_icon($currentLanguageAbr),
                'locale' => $currentLanguageAbr,
                'language' => $currentLanguageAbr
            ];
        }
    }

    public function loadTranslations()
    {
        $filter = [
            'translation_namespace' => $this->getActualNamespace(),
            'page' => $this->currentPage,
            'per_page' => $this->perPage
        ];

        if (!empty($this->search)) {
            $filter['search'] = $this->search;
        }

        $translationsResult = TranslationKey::getGroupedTranslations($filter);
        $this->translations = $translationsResult['results'] ?? [];
    }

    public function updateSearch($search)
    {
        $this->search = $search;
        $this->currentPage = 1;
        $this->loadTranslations();
    }

    public function saveTranslation($translationKey, $locale, $translationText)
    {
        try {
            Config::set('microweber.disable_model_cache', true);

            // Get or create translation key
            $translationKeyRecord = TranslationKey::where('translation_key', $translationKey)
                ->where('translation_namespace', $this->getActualNamespace())
                ->where('translation_group', '*')
                ->first();

            if (!$translationKeyRecord) {
                $translationKeyRecord = new TranslationKey();
                $translationKeyRecord->translation_key = $translationKey;
                $translationKeyRecord->translation_namespace = $this->getActualNamespace();
                $translationKeyRecord->translation_group = '*';
                $translationKeyRecord->save();
            }

            // Get or create translation text
            $translationTextRecord = TranslationText::where('translation_key_id', $translationKeyRecord->id)
                ->where('translation_locale', $locale)
                ->first();

            if (!$translationTextRecord) {
                $translationTextRecord = new TranslationText();
                $translationTextRecord->translation_key_id = $translationKeyRecord->id;
                $translationTextRecord->translation_locale = $locale;
            }

            $translationTextRecord->translation_text = trim($translationText);
            $translationTextRecord->save();

            // Clear caches
            \Cache::tags('translation_keys')->flush();

            Notification::make()
                ->title('Translation saved')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saving translation')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteTranslation($translationKey)
    {
        try {
            $translationKeyRecord = TranslationKey::where('translation_key', $translationKey)
                ->where('translation_namespace', $this->getActualNamespace())
                ->where('translation_group', '*')
                ->first();

            if ($translationKeyRecord) {
                // Delete all translation texts for this key
                TranslationText::where('translation_key_id', $translationKeyRecord->id)->delete();
                
                // Delete the translation key
                $translationKeyRecord->delete();

                // Clear caches
                \Cache::tags('translation_keys')->flush();

                Notification::make()
                    ->title('Translation deleted')
                    ->success()
                    ->send();

                $this->loadTranslations();
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error deleting translation')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function refreshTranslations()
    {
        $this->loadTranslations();
    }

    private function getActualNamespace()
    {
        switch ($this->namespace) {
            case 'global':
                return '*';
            case 'modules':
                return 'modules-*';
            case 'templates':
                return 'templates-*';
            default:
                return $this->namespace;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function nextPage()
    {
        $this->currentPage++;
        $this->loadTranslations();
    }

    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->loadTranslations();
        }
    }

    public function gotoPage($page)
    {
        $this->currentPage = $page;
        $this->loadTranslations();
    }

    public function render()
    {
        return view('modules.settings::livewire.translation-manager');
    }
}
