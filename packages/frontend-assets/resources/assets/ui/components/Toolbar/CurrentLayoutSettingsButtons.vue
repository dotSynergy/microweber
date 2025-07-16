<template>
    <div v-if="currentLayoutModules.length > 0" class="current-layout-modules">

        <hr>

        <!-- Add Module Button -->
        <div class="add-module-section">
            <div
                class="btn-icon add-module-button"
                title="Insert Module"
                @click="insertModuleIntoLayout"
            >
                <v-tooltip activator="parent" location="start">
                    Insert Module
                </v-tooltip>
                <span>
                   <svg fill="currentColor" height="24px" viewBox="0 -960 960 960" width="24px"
                        xmlns="http://www.w3.org/2000/svg"><path d="M440-120v-320H120v-80h320v-320h80v320h320v80H520v320h-80Z"/></svg>
                </span>
            </div>
        </div>

        <div class="modules-buttons">
            <div
                v-for="module in currentLayoutModules"
                :key="module.id"
                :class="{ 'active': module.isActive }"
                :title="module.title || module.type"
                class="btn-icon module-settings-button"
                @click="openModuleSettings(module)"
                @mouseenter="onModuleHover(module)"
            >
                <v-tooltip activator="parent" location="start">
                    {{ module.title || module.type }}
                </v-tooltip>
                <span v-html="getModuleIcon(module)"></span>
            </div>
        </div>

        <hr>

    </div>
</template>

<style scoped>
.current-layout-modules {
    display: flex;
    flex-direction: column;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 10px;
}

.layout-modules-header {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
}

.layout-title {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    text-align: center;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    max-width: 100%;
}

.modules-buttons {
    display: flex;
    flex-direction: column;
    gap: 15px;
    align-items: center;
}

.add-module-section {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
}


.module-settings-button {
    height: 32px;
    width: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
}

.module-settings-button:hover {
    transform: scale(1.05);
}


.module-settings-button:active {
    transform: scale(0.95);
}

.module-settings-button :deep(svg) {
    width: 16px;
    height: 16px;
    fill: currentColor;
}

.module-settings-button :deep(img) {
    width: 16px;
    height: 16px;
    object-fit: contain;
}
</style>

<script>
export default {
    name: 'CurrentLayoutSettingsButtons',
    data() {
        return {
            currentLayoutModules: [],
            currentLayoutTitle: '',
            currentLayoutElement: null,
            updateInterval: null,
            activeModuleId: null
        };
    },
    mounted() {


        mw.app.on('ready', event => {


            this.setupEventListeners();
            this.updateCurrentLayout();

            // Update periodically to catch dynamic changes
            this.updateInterval = setInterval(() => {
                this.updateCurrentLayout();
            }, 2000);

        });
    },
    beforeUnmount() {
        this.cleanupEventListeners();
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
    },
    methods: {
        setupEventListeners() {
            // Listen for layout changes
            if (window.mw?.app?.canvas) {
                window.mw.app.canvas.on('liveEditCanvasLoaded', () => {
                    this.updateCurrentLayout();
                });


                mw.top().app.on('moduleInserted', () => {
                    this.updateCurrentLayout();
                })
                mw.top().app.on('layoutCloned', () => {
                    this.updateCurrentLayout();
                })
                window.mw.app.canvas.on('canvasDocumentClick', () => {
                    // Delay update to allow for layout selection
                    setTimeout(() => {
                        this.updateCurrentLayout();
                        this.updateActiveModule();
                    }, 100);
                });
            }

            // Listen for module settings events
            if (window.mw?.app?.editor) {
                window.mw.app.editor.on('onModuleSettingsRequest', (module) => {
                    this.activeModuleId = module?.id || null;
                });
            }


        },

        cleanupEventListeners() {
            // Clean up any event listeners if needed
        },

        updateCurrentLayout() {
            try {
                const layoutElement = this.getCurrentLayoutElement();

                if (layoutElement !== this.currentLayoutElement) {
                    this.currentLayoutElement = layoutElement;
                    this.extractLayoutModules(layoutElement);
                }
            } catch (error) {
                console.warn('Error updating current layout:', error);
            }
        },

        updateActiveModule() {
            // Update which module is currently active/selected
            const activeModule = window.mw?.top()?.app?.liveEdit?.getSelectedModuleNode();
            this.activeModuleId = activeModule?.id || null;

            // Update the active state in our modules list
            this.currentLayoutModules.forEach(module => {
                module.isActive = module.id === this.activeModuleId;
            });
        },

        getCurrentLayoutElement() {
            // Try to get the currently selected layout
            let layoutElement = window.mw.top().app.liveEdit.getSelectedLayoutNode();

            return layoutElement;
        },        extractLayoutModules(layoutElement) {
            this.currentLayoutModules = [];
            this.currentLayoutTitle = '';

            if (!layoutElement) {
                return;
            }

            // Get layout title
            this.currentLayoutTitle = this.getLayoutTitle(layoutElement);

            // Find only direct child modules within this layout (not nested modules inside other modules)
            const modules = this.getDirectChildModules(layoutElement);
            const moduleData = [];

            modules.forEach((moduleElement, index) => {
                // Check if module is inaccessible
                const isInaccessible = this.isModuleInaccessible(moduleElement);
                if (isInaccessible) {
                    return; // Skip inaccessible modules
                }

                const moduleType = moduleElement.getAttribute('data-type') || moduleElement.getAttribute('type');
                const moduleId = moduleElement.getAttribute('id') || `module-${index}`;
                const moduleTitle = this.getModuleTitle(moduleElement, moduleType);

                if (moduleType && this.isEditableModule(moduleType)) {
                    moduleData.push({
                        id: moduleId,
                        type: moduleType,
                        title: moduleTitle,
                        element: moduleElement,
                        isActive: moduleId === this.activeModuleId
                    });
                }
            });

            // Remove duplicates and limit to most important modules
            this.currentLayoutModules = this.deduplicateModules(moduleData).slice(0, 8);
        },

        getLayoutTitle(layoutElement) {
            // Try to get layout name from various sources
            const layoutName = layoutElement.getAttribute('data-layout') ||
                layoutElement.getAttribute('data-layout-name') ||
                layoutElement.getAttribute('data-title') ||
                layoutElement.querySelector('.layout-title')?.textContent;

            if (layoutName) {
                return layoutName.length > 15 ? layoutName.substring(0, 15) + '...' : layoutName;
            }            return 'Layout';
        },

        getDirectChildModules(layoutElement) {
            // Get only direct child modules, not nested modules inside other modules
            const allModules = layoutElement.querySelectorAll('.module[data-type]:not([data-type=""]):not(.module-layouts)');
            const directChildModules = [];

            allModules.forEach(moduleElement => {
                // Check if this module is a direct child of the layout
                // by verifying that there's no other module between this one and the layout
                let parent = moduleElement.parentElement;
                let isDirectChild = false;

                while (parent && parent !== layoutElement) {
                    // If we encounter another module element on the way up,
                    // this means our module is nested inside another module
                    if (parent.classList.contains('module') &&
                        parent.hasAttribute('data-type') &&
                        parent.getAttribute('data-type') !== '' &&
                        !parent.classList.contains('module-layouts')) {
                        isDirectChild = false;
                        break;
                    }
                    parent = parent.parentElement;
                }

                // If we reached the layout without encountering another module,
                // this is a direct child
                if (parent === layoutElement) {
                    isDirectChild = true;
                }

                if (isDirectChild) {
                    directChildModules.push(moduleElement);
                }
            });

            return directChildModules;
        },

        getModuleTitle(moduleElement, moduleType) {
            // Try to get module info from Microweber's module system first
            if (window.mw?.top()?.app?.modules) {
                const info = window.mw.top().app.modules.getModuleInfo(moduleType);
                if (info && info.name) {
                    return info.name;
                }
            }

            // Try to get a meaningful title for the module
            const title = moduleElement.getAttribute('data-title') ||
                moduleElement.getAttribute('data-module-title') ||
                moduleElement.getAttribute('data-mw-title') ||
                moduleElement.querySelector('.module-title')?.textContent ||
                moduleElement.querySelector('h1, h2, h3, h4')?.textContent;

            if (title) {
                return title.length > 20 ? title.substring(0, 20) + '...' : title;
            }

            // Fallback to formatted module type
            return this.formatModuleType(moduleType);
        },

        formatModuleType(moduleType) {
            if (!moduleType) return 'Module';

            // Convert module type to readable format
            return moduleType
                .replace(/[_-]/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase())
                .trim();
        }, isEditableModule(moduleType) {
            // Filter out non-editable or system modules
            const excludedTypes = [
                'layouts',
                'layout',
                'text',
                'spacer',
                'divider'
            ];

            return moduleType && !excludedTypes.includes(moduleType.toLowerCase());
        },

        isModuleInaccessible(moduleElement) {
            // Use Microweber's built-in inaccessible module check
            if (window.mw?.top()?.app?.liveEdit?.liveEditHelpers?.targetIsInacesibleModule) {
                return window.mw.top().app.liveEdit.liveEditHelpers.targetIsInacesibleModule(moduleElement);
            }

            // Fallback check for inaccessible modules
            return moduleElement.classList.contains('no-settings') ||
                moduleElement.classList.contains('inaccessibleModule') ||
                moduleElement.classList.contains('inaccessibleModuleIfFirstParentIsLayout');
        },

        deduplicateModules(modules) {
            const seen = new Set();
            return modules.filter(module => {
                const key = `${module.type}-${module.title}`;
                if (seen.has(key)) {
                    return false;
                }
                seen.add(key);
                return true;
            });
        },

        getModuleIcon(module) {
            // Use the new getModuleIcon service function directly
            if (window.mw?.top()?.app?.modules) {
                return window.mw.top().app.modules.getModuleIcon(module.type);
            }

            // Fallback to default icon
            return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
        }, openModuleSettings(module) {
            try {
                // Use Microweber's module settings system
                if (window.mw?.top()?.app?.editor?.dispatch && module.element) {
                    // Set the active module ID
                    this.activeModuleId = module.id;
                    this.updateActiveModule();

                    // Dispatch the module settings request
                    window.mw.top().app.editor.dispatch('onModuleSettingsRequest', module.element);
                } else if (window.mw?.top()?.openModuleSettings && module.id) {
                    // Alternative method using global openModuleSettings
                    this.activeModuleId = module.id;
                    this.updateActiveModule();
                    window.mw.top().openModuleSettings(module.id);
                } else if (window.mw?.app?.moduleSettings?.openSettings) {
                    // Fallback method
                    this.activeModuleId = module.id;
                    this.updateActiveModule();
                    window.mw.app.moduleSettings.openSettings(module.element);
                } else {
                    console.warn('Module settings method not available');
                }
            } catch (error) {
                console.error('Error opening module settings:', error);
            }
        }, onModuleHover(module) {
            try {
                // Set the target element handle on hover
                if (window.mw?.top()?.app?.liveEdit?.elementHandle?.set && module.element) {

                    if(module.type !== 'layouts' && module.type != 'background'){
                        window.mw.top().app.liveEdit.elementHandle.set(module.element, true);

                    }



                }
            } catch (error) {
                console.error('Error setting element handle on hover:', error);
            }
        },

        insertModuleIntoLayout() {
            try {
                // Get the current layout element as the target

                const targetElement =
                    window.mw.top().app.liveEdit.getSelectedNode()
                    || window.mw.top().app.liveEdit.getSelectedElementNode()
                    || mw.top().app.liveEdit.elementHandle.getTarget()
                    || this.getCurrentLayoutElement();


                console.log('Inserting module into element:', targetElement);
                if (targetElement && window.mw?.app?.editor?.dispatch) {
                    // Dispatch the insert module request with the layout as target
                    window.mw.app.editor.dispatch('insertModuleRequest', targetElement);
                } else {
                    console.warn('Cannot insert module: target element or editor not available');
                }
            } catch (error) {
                console.error('Error inserting module into layout:', error);
            }
        }
    }
};
</script>
