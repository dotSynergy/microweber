<x-filament-panels::page>
    <div id="file-browser-page-display" class="mb-3 mt-3" style="min-height: 500px;"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var fileManager = mw.FileManager({
                element: '#file-browser-page-display',
                canSelectFolder: true,
                stickyHeader: true,
                type: '*',
                selectable: true,
                multiselect: true,
                options: true,
                selectableRow: true,
            });

            fileManager.on('insert', function (files) {
                console.log('Selected files:', files);
            });

            fileManager.on('selectionChanged', function (selection) {
                console.log('Selection changed:', selection);
            });
        });
    </script>
</x-filament-panels::page>

