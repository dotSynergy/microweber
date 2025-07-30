<div class="translation-manager">
    <div class="mb-6">
        <!-- Search Input -->
        <div class="relative">
            <input type="text"
                   wire:model.debounce.500ms="search"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                   placeholder="Search translations...">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    @if(count($translations) > 0)
        <!-- Translations List -->
        <div class="space-y-6">
            @foreach($translations as $translationKey => $translationByLocales)
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <!-- Translation Key Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-sm text-primary-600 dark:text-primary-400 mb-1">Translation Key</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $translationKey }}</div>
                        </div>
                        <button type="button"
                                wire:click="deleteTranslation('{{ $translationKey }}')"
                                onclick="return confirm('Are you sure you want to delete this translation?')"
                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Translation Inputs -->
                    <div class="space-y-4">
                        @foreach($supportedLanguages as $language)
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                                <!-- Language Label -->
                                <div class="md:col-span-2 flex items-center space-x-2">
                                    <span class="flag-icon flag-icon-{{ $language['icon'] ?? 'us' }} flex-shrink-0"></span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $language['language'] ?? $language['locale'] }}
                                    </span>
                                </div>

                                <!-- Translation Input -->
                                <div class="md:col-span-10">
                                    @php
                                        $currentTranslation = $translationByLocales[$language['locale']] ?? '';
                                        if (empty($currentTranslation) && strpos($language['locale'], 'en') !== false) {
                                            $currentTranslation = $translationKey;
                                        }
                                    @endphp

                                    <textarea
                                        wire:model.lazy="editingTranslations.{{ md5($translationKey . $language['locale']) }}"
                                        wire:change="saveTranslation('{{ $translationKey }}', '{{ $language['locale'] }}', $event.target.value)"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 transition-colors duration-200"
                                        rows="2"
                                        placeholder="Enter translation for {{ $language['language'] ?? $language['locale'] }}">{{ $currentTranslation }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Page {{ $currentPage }}
            </div>

            <div class="flex space-x-2">
                @if($currentPage > 1)
                    <button type="button"
                            wire:click="previousPage"
                            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        Previous
                    </button>
                @endif

                <button type="button"
                        wire:click="nextPage"
                        class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    Next
                </button>
            </div>
        </div>

    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1l-4 4z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No translations found</h3>
            <p class="text-gray-600 dark:text-gray-400">
                @if(!empty($search))
                    No translations match your search criteria.
                @else
                    @if($namespace === 'global')
                        No global translations are available in the database.
                    @elseif($namespace === 'modules')
                        No module translations are available. Install modules with translation support first.
                    @else
                        No template translations are available. Install templates with translation support first.
                    @endif
                @endif
            </p>

            @if(!empty($search))
                <button type="button"
                        wire:click="$set('search', '')"
                        class="mt-4 text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                    Clear search
                </button>
            @endif
        </div>
    @endif
    <style>
        .flag-icon {
            width: 20px;
            height: 15px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            display: inline-block;
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <!-- Loading Overlay -->
    <div wire:loading class="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
            <span class="text-gray-900 dark:text-gray-100">Loading...</span>
        </div>
    </div>
</div>

