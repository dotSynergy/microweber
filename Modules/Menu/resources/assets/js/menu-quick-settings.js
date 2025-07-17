let moduleMenuQickSettings = [
    // {
    //     title: 'Add Menu Item',
    //     icon: function(module) {
    //         if (window.mw?.top()?.app?.modules) {
    //             return window.mw.top().app.modules.getModuleIcon('menu');
    //         }
    //         return `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6V5a1 1 0 0 1 1-1z" fill="currentColor"/></svg>`;
    //     },
    //     action: function (el) {
    //         let moduleId = el.getAttribute('id');
    //         window.mw.top().app.editor.dispatch('onModuleSettingsRequest', el);
    //
    //       //  alert('Add Menu Item action triggered for module ID: ' + moduleId);
    //
    //         // mw.app.liveEdit.handle.moduleSettings.openModule({
    //         //     id: moduleId,
    //         //     active_tab: 'Layout Settings'
    //         // });
    //     }
    // },
    {
        title: 'Menu Settings',
   //     titleVisible:true,

        icon: function(module) {
            if (window.mw?.top()?.app?.modules) {
                return window.mw.top().app.modules.getModuleIcon('menu');
            }
            return `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2c-.35 0-.687.057-1.017.169l-.8 2.4a7.085 7.085 0 0 0-1.732 1l-2.343-.98a3.986 3.986 0 0 0-1.449 1.449l.98 2.343a7.085 7.085 0 0 0-1 1.732l-2.4.8A3.986 3.986 0 0 0 2 12c0 .35.057.687.169 1.017l2.4.8a7.085 7.085 0 0 0 1 1.732l-.98 2.343a3.986 3.986 0 0 0 1.449 1.449l2.343-.98a7.085 7.085 0 0 0 1.732 1l.8 2.4A3.986 3.986 0 0 0 12 22c.35 0 .687-.057 1.017-.169l.8-2.4a7.085 7.085 0 0 0 1.732-1l2.343.98a3.986 3.986 0 0 0 1.449-1.449l-.98-2.343a7.085 7.085 0 0 0 1-1.732l2.4-.8A3.986 3.986 0 0 0 22 12c0-.35-.057-.687-.169-1.017l-2.4-.8a7.085 7.085 0 0 0-1-1.732l.98-2.343a3.986 3.986 0 0 0-1.449-1.449l-2.343.98a7.085 7.085 0 0 0-1.732-1l-.8-2.4A3.986 3.986 0 0 0 12 2zm0 4a6 6 0 1 0 0 12 6 6 0 0 0 0-12z" fill="currentColor"/></svg>`;
        },
        action: function (el) {
            let moduleId = el.getAttribute('id');
            window.mw.top().app.editor.dispatch('onModuleSettingsRequest', el);
           // alert('Menu Settings action triggered for module ID: ' + moduleId);

            // mw.app.liveEdit.handle.moduleSettings.openModule({
            //     id: moduleId,
            //     active_tab: 'Design'
            // });
        }
    }
];


mw.quickSettings.menu = moduleMenuQickSettings;
