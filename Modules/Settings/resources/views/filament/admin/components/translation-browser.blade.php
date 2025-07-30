@php
    $namespace = $namespace ?? 'global';
    $title = $title ?? 'Translations';
    $namespaceMd5 = md5($namespace);
@endphp

<div class="translation-browser" data-namespace="{{ $namespace }}">
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $title }}</h3>
            
            <div class="flex space-x-2">
                <button type="button" 
                        onclick="openImportModal('{{ $namespace }}')" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    Import
                </button>
                
                <button type="button" 
                        onclick="openExportModal('{{ $namespace }}')" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l3-3m0 0l-3-3m3 3H9"></path>
                    </svg>
                    Export
                </button>
            </div>
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            @if($namespace === 'global')
                Manage global translation strings used throughout your website. These translations apply to core system messages, common interface elements, and general content.
            @elseif($namespace === 'modules')
                Manage translations specific to installed modules. Each module can have its own set of translatable strings for module-specific functionality.
            @else
                Manage translations for template-specific content. These translations are used by themes and custom templates to display localized content.
            @endif
        </p>
    </div>

    <!-- Translation Management Component -->
    @livewire('modules.settings::translation-manager', ['namespace' => $namespace])
</div>

<script>
function openImportModal(namespace) {
    // This would integrate with the Filament modal system or existing import functionality
    console.log('Opening import modal for namespace:', namespace);
    
    // For now, show info about the functionality
    if (window.Filament) {
        window.Filament.notification()
            .title('Import Feature')
            .body('Import functionality will integrate with the existing translation import system from the header actions.')
            .info()
            .send();
    } else {
        alert('Import functionality will integrate with the existing translation import system from the header actions.');
    }
}

function openExportModal(namespace) {
    // This would integrate with the Filament modal system or existing export functionality
    console.log('Opening export modal for namespace:', namespace);
    
    // For now, show info about the functionality
    if (window.Filament) {
        window.Filament.notification()
            .title('Export Feature')
            .body('Export functionality will integrate with the existing translation export system from the header actions.')
            .info()
            .send();
    } else {
        alert('Export functionality will integrate with the existing translation export system from the header actions.');
    }
}
</script>
