<template>
    <div v-show="isModuleElement">
        <div v-if="isModuleElement" class="current-node-module-edit">
            <div
                :class="{ 'active': isEditingModule }"
                class="btn-icon module-edit-button"
                title="Module Settings"
                @click="editCurrentModule"
                @mouseenter="onModuleHover"
            >
                <v-tooltip activator="parent" location="start">
                    Module Settings
                </v-tooltip>
                <span v-html="getModuleEditIcon()"></span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.current-node-module-edit {
    display: flex;
    align-items: center;
    padding: 5px 0;
}

.module-edit-button {
    height: 32px;
    width: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
}

.module-edit-button:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
}

.module-edit-button.active {
    background: rgba(33, 37, 41, 0.16);
    border-color: rgb(56, 54, 54);
    color: #383636;
    box-shadow: 0 0 4px rgb(70, 73, 84);
}

.module-edit-button:active {
    transform: scale(0.95);
}

.module-edit-button :deep(svg) {
    width: 24px;
    height: 24px;
    fill: currentColor;
}

.module-edit-button :deep(img) {
    width: 16px;
    height: 16px;
    object-fit: contain;
}
</style>

<script>
export default {
    name: 'CurrentNodeModuleEditButton',
    data() {
        return {
            currentElement: null,
            isModuleElement: false,
            isEditingModule: false,
            updateInterval: null,
            // Store event handler references for cleanup
            eventHandlers: {
                liveEditCanvasLoaded: null,
                canvasDocumentClick: null,
                moduleSettingsRequest: null,
                moduleSettingsEnd: null
            }
        };
    },
    mounted() {
        mw.app.on('ready', event => {
            this.setupEventListeners();
            this.updateCurrentNode();

            // Update periodically to catch dynamic changes
            // this.updateInterval = setInterval(() => {
            //     this.updateCurrentNode();
            // }, 1000);
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
            // Listen for canvas changes
            if (window.mw?.app?.canvas) {
                this.eventHandlers.liveEditCanvasLoaded = () => {
                    this.updateCurrentNode();
                };

                this.eventHandlers.canvasDocumentClick = () => {
                    // Delay update to allow for element selection
                    setTimeout(() => {
                        this.updateCurrentNode();
                    }, 100);
                };

                window.mw.app.canvas.on('liveEditCanvasLoaded', this.eventHandlers.liveEditCanvasLoaded);
                window.mw.app.canvas.on('canvasDocumentClick', this.eventHandlers.canvasDocumentClick);
            }

            // Listen for module editor events
            if (window.mw?.app?.editor) {
                this.eventHandlers.moduleSettingsRequest = () => {
                    this.isEditingModule = true;
                };

                this.eventHandlers.moduleSettingsEnd = () => {
                    this.isEditingModule = false;
                    this.updateCurrentNode(); // Refresh current node after editing ends
                };

                window.mw.app.editor.on('onModuleSettingsRequest', this.eventHandlers.moduleSettingsRequest);
                window.mw.app.editor.on('onModuleSettingsEnd', this.eventHandlers.moduleSettingsEnd);
            }
        },

        cleanupEventListeners() {
            // Clean up canvas event listeners
            if (window.mw?.app?.canvas) {
                if (this.eventHandlers.liveEditCanvasLoaded) {
                    window.mw.app.canvas.off('liveEditCanvasLoaded', this.eventHandlers.liveEditCanvasLoaded);
                }
                if (this.eventHandlers.canvasDocumentClick) {
                    window.mw.app.canvas.off('canvasDocumentClick', this.eventHandlers.canvasDocumentClick);
                }
            }

            // Clean up editor event listeners
            if (window.mw?.app?.editor) {
                if (this.eventHandlers.moduleSettingsRequest) {
                    window.mw.app.editor.off('onModuleSettingsRequest', this.eventHandlers.moduleSettingsRequest);
                }
                if (this.eventHandlers.moduleSettingsEnd) {
                    window.mw.app.editor.off('onModuleSettingsEnd', this.eventHandlers.moduleSettingsEnd);
                }
            }

            // Clear event handler references
            this.eventHandlers = {
                liveEditCanvasLoaded: null,
                canvasDocumentClick: null,
                moduleSettingsRequest: null,
                moduleSettingsEnd: null
            };
        },

        updateCurrentNode() {
            try {
                const activeElement = mw.top().app.liveEdit.elementHandle.getTarget()
                      || window.mw.top().app.liveEdit.getSelectedNode()
                      || window.mw.top().app.liveEdit.getSelectedElementNode();

                if (activeElement !== this.currentElement) {
                    this.currentElement = activeElement;
                    this.checkIfModuleElement();
                }
            } catch (error) {
                console.warn('Error updating current node:', error);
                this.currentElement = null;
                this.isModuleElement = false;
            }
        },

        checkIfModuleElement() {
            if (!this.currentElement) {
                this.isModuleElement = false;
                return;
            }

            // Check if the current element is a module
            this.isModuleElement = this.isEditableModule(this.currentElement);

            // Check if module settings are currently open
            if (this.isModuleElement) {
                this.checkModuleEditingState();
            }
        },

        isEditableModule(element) {
            if (!element) return false;

            // Check if element is a module
            const isModule = element.classList.contains('module') ||
                            element.hasAttribute('data-type') ||
                            element.hasAttribute('data-module');

            if (!isModule) return false;

            // Check if module is inaccessible
            if (this.isModuleInaccessible(element)) {
                return false;
            }

            // Get module type
            const moduleType = element.getAttribute('data-type') ||
                              element.getAttribute('type') ||
                              element.getAttribute('data-module');

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

        checkModuleEditingState() {
            // Check if module settings are currently open for this module
            try {
                const moduleId = this.currentElement.getAttribute('id');
                const isSettingsOpen = window.mw?.top()?.app?.liveEdit?.getActiveModuleSettings?.() === moduleId;
                this.isEditingModule = isSettingsOpen || false;
            } catch (error) {
                this.isEditingModule = false;
            }
        },

        editCurrentModule() {
            try {
                if (!this.currentElement) {
                    console.warn('No current module element to edit');
                    return;
                }

                if (!this.isEditableModule(this.currentElement)) {
                    console.warn('Current element is not an editable module');
                    return;
                }

                // Set the editing state
                this.isEditingModule = true;

                // Trigger module settings request
                window.mw.app.editor.dispatch('onModuleSettingsRequest', this.currentElement);
            } catch (error) {
                console.error('Error editing current module:', error);
                this.isEditingModule = false;
            }
        },

        onModuleHover() {
            // Optional: Add hover effects or preview functionality
            try {
                if (this.currentElement && this.isModuleElement) {
                    // Could add module highlighting or preview here
                }
            } catch (error) {
                console.warn('Error on module hover:', error);
            }
        },

        getModuleEditIcon() {
            // Try to get module settings icon from Microweber's icon service
            if (window.mw?.top()?.app?.iconService?.icon) {
                const icon = window.mw.top().app.iconService.icon('module-settings')  ;

                if (icon) {
                    return icon;
                }
            }

            // Fallback to a basic settings/cog icon
            return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 15.5A3.5 3.5 0 0 1 8.5 12A3.5 3.5 0 0 1 12 8.5a3.5 3.5 0 0 1 3.5 3.5a3.5 3.5 0 0 1-3.5 3.5m7.43-2.53c.04-.32.07-.64.07-.97c0-.33-.03-.66-.07-1l2.11-1.63c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.31-.61-.22l-2.49 1c-.52-.39-1.06-.73-1.69-.98l-.37-2.65A.506.506 0 0 0 14 2h-4c-.25 0-.46.18-.5.42l-.37 2.65c-.63.25-1.17.59-1.69.98l-2.49-1c-.22-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64L4.57 11c-.04.34-.07.67-.07 1c0 .33.03.65.07.97l-2.11 1.66c-.19.15-.25.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1.01c.52.4 1.06.74 1.69.99l.37 2.65c.04.24.25.42.5.42h4c.25 0 .46-.18.5-.42l.37-2.65c.63-.26 1.17-.59 1.69-.99l2.49 1.01c.22.08.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.66Z"/></svg>';
        }
    }
};
</script>
