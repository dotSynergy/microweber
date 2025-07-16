import axios from 'axios';

export const Modules = {

    modulesListData: null,


    list:   function (cb) {

        if (this.modulesListData) {
            if (cb) {
                cb.call(undefined, this.modulesListData)
            }
            return this.modulesListData;
        }

        axios.get(route('api.module.list') + '?layout_type=module')
        .then((response) => {

            this.modulesListData = response.data;


            if (cb) {
                cb.call(undefined, this.modulesListData)
            }
        });



    },
    modulesSkinsData: [],
    getSkins: async function (module) {
        if (this.modulesSkinsData[module]) {
            return this.modulesSkinsData[module];
        }

        await axios.get(route('api.module.getSkins') + '?module=' + module)
            .then((response) => {
                this.modulesSkinsData[module] = response.data;
            });

        if (this.modulesSkinsData[module]) {
            return this.modulesSkinsData[module];
        }

    },


    getModuleInfo: function (module) {
        if (this.modulesListData && this.modulesListData.modules) {
            var foundModule = this.modulesListData.modules.find(function (element) {
                return element.module == module;
            });

            if (foundModule) {
                // Process icon if exists
                if (foundModule.icon) {
                    if (foundModule.icon.startsWith('data:image/svg+xml;base64,')) {
                        foundModule.processedIcon = `<img src="${foundModule.icon}" alt="${foundModule.name || module}" style="width: 24px; height: 24px;" />`;
                    } else if (foundModule.icon.includes('<svg')) {
                        foundModule.processedIcon = foundModule.icon;
                    } else if (foundModule.icon.startsWith('http') || foundModule.icon.startsWith('/')) {
                        foundModule.processedIcon = `<img src="${foundModule.icon}" alt="${foundModule.name || module}" style="width: 16px; height: 16px;" />`;
                    } else {
                        foundModule.processedIcon = foundModule.icon;
                    }
                } else {
                    // Default icon if none provided
                    foundModule.processedIcon = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
                }
            }

            return foundModule;
        }
    },

    getModuleIcon: function (module) {
        const moduleType = typeof module === 'string' ? module : module.type;
        const info = this.getModuleInfo(moduleType);

        if (info && info.processedIcon) {
            return info.processedIcon;
        }

        // Default icon if none found
        return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    },

    getModuleInlineViewData: function (moduleId) {
        try {

            const canvasDoc = window.mw.top().app.canvas.getDocument();

            let moduleElement;
            let actualModuleId;

            // Check if moduleId is a DOM node or a string
            if (typeof moduleId === 'string') {
                // Find the module element in the canvas document
                moduleElement = canvasDoc.getElementById(moduleId);
                actualModuleId = moduleId;
            } else if (moduleId && moduleId.nodeType === Node.ELEMENT_NODE) {
                // moduleId is a DOM node
                moduleElement = moduleId;
                actualModuleId = moduleElement.id;
            } else {
                console.warn('Invalid moduleId parameter: must be a string or DOM element');
                return null;
            }

            if (!moduleElement) {
                console.warn(`Module element with ID ${actualModuleId} not found`);
                return null;
            }

            // Look for script tag with specific data-module-settings-id attribute
            const scriptTag = moduleElement.querySelector(`script[data-module-settings-id="${actualModuleId}"]`);
            if (!scriptTag) {
                console.warn(`Script tag with data-module-settings-id="${actualModuleId}" not found in module ${actualModuleId}`);
                return null;
            }

            const scriptContent = scriptTag.innerHTML;

            const encodedData =scriptContent;
            const decodedData = encodedData
                .replace(/&quot;/g, '"')
                .replace(/&amp;/g, '&')
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>')
                .replace(/&#039;/g, "'");

            try {
                const parsedData = JSON.parse(decodedData);
                return parsedData;
            } catch (parseError) {
                console.error(`Failed to parse JSON data for module ${actualModuleId}:`, parseError);
                return null;
            }

        } catch (error) {
            console.error(`Error extracting module data for ${actualModuleId}:`, error);
            return null;
        }
    },

}


Modules.list();
