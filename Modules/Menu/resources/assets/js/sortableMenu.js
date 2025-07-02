export default function sortableMenu() {
    return {
        async init() {
            const collectTreeElements = target => {
                if (!target) {
                    console.log('target is not defined')
                    return { ids: [], ids_parents: {} };
                }

                let container = $(target).closest('[data-menu-id]');
                if (!container.length) {
                    container = $('[data-menu-id]').first();
                }

                const closestMenuId = container.attr('data-menu-id') || 0;

                const result = {
                    menu_id: closestMenuId,
                    ids: [],
                    ids_parents: {}
                };

                // Find all menu items in order
                container.find('.menu_element').each(function() {
                    const id = $(this).attr('data-item-id');
                    if (id) {
                        result.ids.push(id);
                        const parentEl = $(this).parents('.menu_element:first');
                        const parentId = parentEl.attr('data-item-id');
                        if (parentId) {
                            result.ids_parents[id] = parentId;
                        } else {
                            const fallbackParent = $('#ed_menu_holder').find('[name="parent_id"]').first().val();
                            result.ids_parents[id] = fallbackParent || closestMenuId;
                        }
                    }
                });

                return result;
            };

            let _orderChangeHandleTimeout = null;

            const saveMenuOrder = async (target) => {
                try {
                    const result = collectTreeElements(target);
                    await $.post(route('api.menu.item.reorder'), result);
                    if (mw.notification) {
                        mw.notification.success('Menu changes are saved');
                    }

                    Livewire.dispatch('menuOrderUpdated', { menuId: result.menu_id, ids: result.ids, ids_parents: result.ids_parents });

                } catch (error) {
                    console.error('Error saving menu order:', error);
                    if (mw.notification) {
                        mw.notification.error('Could not save menu changes');
                    }
                }
            };

            const _orderChangeHandle = function(e, ui) {
                clearTimeout(_orderChangeHandleTimeout);
                _orderChangeHandleTimeout = setTimeout(function() {
                    saveMenuOrder(e.target);
                }, 100);
            };

            // Initialize sortable
            $('.admin-menu-items-holder ul').nestedSortable({
                items: "li",
                listType: 'ul',
                handle: ".cursor-move",
                update: _orderChangeHandle
            });

            // Click handlers for menu elements
            $('.admin-menu-items-holder .menu_element_link').each(function() {
                if ($(this).hasClass('binded-click')) {
                    return;
                }
                $(this).addClass('binded-click');

                $(this).on('click', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    const id = $(e.target).attr('data-item-id');
                    if (id) {
                        this.$wire.mountAction('editAction', {id: id});
                    }
                });
            });
        }
    };
}
