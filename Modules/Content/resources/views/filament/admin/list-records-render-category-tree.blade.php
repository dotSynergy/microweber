<div>
    <x-filament::button
        wire:click="$dispatch('showCategoriesSelectorPanel')"
        data-mw-category-selector="true"
        icon="heroicon-m-list-bullet"
        icon-position="before"
        tooltip="Show Categories"
        color="mw-secondary"
    >
        Categories
    </x-filament::button>


</div>


@script
<script>

    document.addEventListener('livewire:initialized', () => {

        let treeControllBox = false, pagesTree;

        Livewire.on('showCategoriesSelectorPanel', async () => {
            if (!treeControllBox) {
                const id = mw.id();
                treeControllBox = new mw.controlBox({
                    content: `<div id="${id}" style="min-width: 250px;padding: 50px 0 0 30px"></div>`,
                    position: 'left',
                    id: `${id}`,
                    closeButton: true
                });
                treeControllBox.show()
                pagesTree = await mw.widget.tree(`#${id}`, {
                    selectableNodes: false,
                    selectable: false,
                    singleSelect: true,
                }, 'tree');

                pagesTree.tree.on('selectionChange', e => {
                    const result = pagesTree.tree.getSelected();
                    let module = document.querySelector('.fi-page');

                    // var selected = tree.getSelected();
                    if (result) {

                        //get only categories

                        var cats = [];
                        result.forEach(function (item) {
                            if (item.type === 'category') {
                                cats.push(item);
                            }
                        });
                        if( cats.length === 0){
                            const component = Livewire.find(module.getAttribute('wire:id'));
                            if(component) {
                                component.set('tableFilters.category_id.value', '');
                                component.$refresh();
                            }
                            return;
                        }

                        const component = Livewire.find(module.getAttribute('wire:id'));
                        if(component) {
                            component.set('tableFilters.category_id.value', cats[0].id);
                            component.$refresh();
                        }

                    }

                })

            } else {
                treeControllBox.toggle();
            }


        });
    })
</script>

@endscript

