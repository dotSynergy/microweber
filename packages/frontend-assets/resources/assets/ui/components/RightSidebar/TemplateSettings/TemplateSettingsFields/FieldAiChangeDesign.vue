<template>
    <div class="position-relative">
        <div ref="aiChatFormBox"></div>
        <div v-if="loading" class="text-center mt-2">AI is thinking...</div>
        <div v-if="error" class="text-danger mt-2">{{ error }}</div>
    </div>
</template>

<script>

import {AIChatForm} from '../../../../../components/ai-chat';


export default {
    inject: ['templateSettings'],
    data() {
        return {
            supportedFonts: [],
            aiFormType: 'simple', // Default to simple form
            aiChatFormInstance: null,
            loading: false,
            error: null,
            aiMessage: ''
        }
    },
    computed: {
        isAIAvailable() {
            // Check if parent has isAIAvailable property
            if (this.templateSettings && this.templateSettings.isAIAvailable !== undefined) {
                return this.templateSettings.isAIAvailable;
            }

            // If not accessible from parent, check it directly
            return typeof window.mw?.top()?.win?.MwAi === 'function';
        }
    },
    methods: {
        prepareAndCleanTemplateStylesAndSelectorsData(items) {
            if (!Array.isArray(items)) return items;

            return items.filter(item => {
                // Remove items with fieldType clearAll
                if (item.fieldType === 'clearAll') return false;

                // Clean unwanted properties
                ['readSettingsFromFiles', 'parent', 'backUrl', 'url'].forEach(prop => {
                    if (item.hasOwnProperty(prop)) delete item[prop];
                });

                // Clean nested settings
                if (item.settings && Array.isArray(item.settings)) {
                    item.settings = this.prepareAndCleanTemplateStylesAndSelectorsData(item.settings);
                }

                // Clean nested fieldSettings if it's an array
                if (item.fieldSettings && Array.isArray(item.fieldSettings)) {
                    item.fieldSettings = this.prepareAndCleanTemplateStylesAndSelectorsData(item.fieldSettings);
                }

                return item.settings?.length > 0 || item.fieldSettings || item.selectors;
            });
        },

        prepareTemplateValuesForEdit(designSelectors) {
            // Filter out items without settings after cleaning
            designSelectors = designSelectors.filter(item => {
                return item.settings && Array.isArray(item.settings) && item.settings.length > 0;
            });

            // Array to collect all selector-property combinations
            let allSelectorPropertyPairs = [];

            // Collect all selector-property pairs
            for (let i = 0; i < designSelectors.length; i++) {
                let item = designSelectors[i];

                // Process nested settings
                for (let k = 0; k < item.settings.length; k++) {
                    let setting = item.settings[k];

                    if (setting.selectors && setting.selectors.length > 0 && setting.fieldSettings) {
                        const nestedSelector = setting.selectors[0];

                        // Handle nested fieldSettings as an object
                        if (!Array.isArray(setting.fieldSettings) && typeof setting.fieldSettings === 'object') {
                            const property = setting.fieldSettings.property;
                            if (property) {
                                allSelectorPropertyPairs.push({
                                    selector: nestedSelector,
                                    property: property,
                                    target: {
                                        object: setting.fieldSettings,
                                        key: 'value'
                                    },
                                    layout: setting.layout || item.layout // collect layout info if present
                                });
                            }
                        }
                        // Handle nested fieldSettings as an array
                        else if (Array.isArray(setting.fieldSettings) && setting.fieldSettings.length > 0) {
                            for (let m = 0; m < setting.fieldSettings.length; m++) {
                                const property = setting.fieldSettings[m].property;
                                if (property) {
                                    allSelectorPropertyPairs.push({
                                        selector: nestedSelector,
                                        property: property,
                                        target: {
                                            object: setting.fieldSettings[m],
                                            key: 'value'
                                        },
                                        layout: setting.layout || item.layout // collect layout info if present
                                    });
                                }
                            }
                        }
                    }
                }
            }

            // Filter unique selector-property combinations
            let uniquePairs = [];
            let uniqueKeys = new Set();

            for (const pair of allSelectorPropertyPairs) {
                const key = `${pair.selector}|${pair.property}`;
                if (!uniqueKeys.has(key)) {
                    uniqueKeys.add(key);
                    uniquePairs.push(pair);
                }
            }            // Apply layout-specific filtering
            if (this.templateSettings.applyMode === 'layout' && this.templateSettings.activeLayoutId && this.templateSettings.activeLayoutId !== 'None') {
                const layoutId = this.templateSettings.activeLayoutId;

                if (layoutId) {
                    const layoutSelectorTarget = '#' + layoutId;
                    const processedPairs = [];

                    for (const pair of uniquePairs) {
                        let currentSelector = pair.selector;
                        let finalSelector = pair.selector;

                        if (currentSelector === ':root') {
                            finalSelector = layoutSelectorTarget;
                        }

                        let includeThisPair = false;
                        if (finalSelector === layoutSelectorTarget) {
                            includeThisPair = true;
                        } else if (pair.layout && pair.layout == layoutId) {
                            includeThisPair = true;
                        }

                        if (includeThisPair) {
                            // Create a shallow copy to modify selector, keeping target reference intact
                            const pairToAdd = {...pair};
                            pairToAdd.selector = finalSelector; // Ensure the selector is the final one
                            processedPairs.push(pairToAdd);
                        }
                    }
                    uniquePairs = processedPairs;

                    // Deduplicate again, as different original pairs might now target the same finalSelector and property
                    const finalUniquePairsAfterTransform = [];
                    const finalUniqueKeysAfterTransform = new Set();
                    for (const p of uniquePairs) {
                        const key = `${p.selector}|${p.property}`;
                        if (!finalUniqueKeysAfterTransform.has(key)) {
                            finalUniqueKeysAfterTransform.add(key);
                            finalUniquePairsAfterTransform.push(p);
                        }
                    }
                    uniquePairs = finalUniquePairsAfterTransform;
                } else {
                    uniquePairs = []; // No layout ID, so no pairs if in layout mode
                }
            }

            // Get property values for each selector-property pair
            for (const pair of uniquePairs) {
                let propertyValue;

                // Use either Vue component instance or direct cssEditor access
                if (this.templateSettings && typeof this.templateSettings.getCssPropertyValue === 'function') {
                    propertyValue = this.templateSettings.getCssPropertyValue(pair.selector, pair.property);
                } else if (window.mw?.top()?.app?.cssEditor) {
                    propertyValue = window.mw.top().app.cssEditor.getPropertyForSelector(pair.selector, pair.property);
                }

                if (pair.target && typeof pair.target.object === 'object' && pair.target.object !== null) {
                    pair.target.object[pair.target.key] = propertyValue;
                }
            }

            // Build final values object for editing
            let valuesForEdit = {};
            for (const pair of uniquePairs) {
                let propertyValue;

                // Use either Vue component instance or direct cssEditor access
                if (this.templateSettings && typeof this.templateSettings.getCssPropertyValue === 'function') {
                    propertyValue = this.templateSettings.getCssPropertyValue(pair.selector, pair.property);
                } else if (window.mw?.top()?.app?.cssEditor) {
                    propertyValue = window.mw.top().app.cssEditor.getPropertyForSelector(pair.selector, pair.property);
                }

                let selectorKey = pair.selector;

                if (!valuesForEdit[selectorKey]) {
                    valuesForEdit[selectorKey] = {};
                }

                valuesForEdit[selectorKey][pair.property] = propertyValue;
            }            // Ensure valuesForEdit has the layout key if empty in layout mode
            if (Object.keys(valuesForEdit).length === 0 &&
                this.templateSettings.applyMode === 'layout' &&
                this.templateSettings.activeLayoutId && this.templateSettings.activeLayoutId !== 'None') {
                const layoutId = this.templateSettings.activeLayoutId;
                if (layoutId) {
                    valuesForEdit['#' + layoutId] = {};
                }
            }

            return valuesForEdit;
        },

        initAIChatForm() {
            this.aiChatFormInstance = new AIChatForm({
                multiLine: true,
                submitOnEnter: true,
                placeholder: 'Make it blue and white...'
            });


            // todo, must clear the box value

            this.$refs.aiChatFormBox.appendChild(this.aiChatFormInstance.form);
            this.aiChatFormInstance.on('submit', (value) => {
                this.aiMessage = value;
                this.changeDesign(value);
            });
            this.aiChatFormInstance.on('areaValue', (value) => {
                this.aiMessage = value;
            });

        },

        submitAiRequest() {
            if (!this.aiMessage.trim()) {
                return;
            }

            this.changeDesign(this.aiMessage);
        },

        async changeDesign(about) {
            if (!about) {
                return; // No message provided
            }

            // Double-check AI availability before proceeding
            if (!this.isAIAvailable) {
                this.error = 'AI functionality is not available';
                return;
            }

            if (!window.mw_template_settings_styles_and_selectors) {
                this.error = 'Template settings are not available';
                return;
            }

            this.loading = true;
            let designSelectors = JSON.parse(JSON.stringify(window.mw_template_settings_styles_and_selectors));

            // First, recursively remove clearAll items and unwanted properties
            designSelectors = this.prepareAndCleanTemplateStylesAndSelectorsData(designSelectors);

            // Prepare values for editing
            const valuesForEdit = this.prepareTemplateValuesForEdit(designSelectors);

            console.log('valuesForEdit:', valuesForEdit);
            let editSchema = JSON.stringify(valuesForEdit);

            // Only include supported fonts if the request contains the word "font"
            let fontInfo = '';
            if (about && about.toLowerCase().includes('font')) {
                let supportedFonts = this.supportedFonts.map(font => font).join(', ');
                fontInfo = `If the user asks to change the font, you must use one of the following fonts: ${supportedFonts} ,`;
            }

            const message = `Using the existing object IDS,
By using this schema: \n ${editSchema} \n
You must write CSS values to the given object,
You are CSS values editor, you must edit the values of the css to complete the user design task,

${fontInfo}

The css design task is to make the design: ${about}

You must write the text for the website and fill the existing object IDs with the text,

You must respond ONLY with the JSON schema with the following structure. Do not add any additional comments""" + \\
"""[
  JSON
{
   { Populated Schema Definition with the items filled with text ... populate the schema with the existing object IDs and the text  }
"""`;

            let messageOptions = {schema: editSchema};

            // Show spinner while waiting for AI response
            window.mw.top().spinner({element: window.mw.top().doc.body, size: 60, decorate: true}).show();

            let messages = [{role: 'user', content: message}];

            try {
                // Send to MwAi
                if (!window.mw?.top()?.win?.MwAi) {
                    throw new Error('AI functionality is not available');
                }

                let res = await window.mw.top().win.MwAi().sendToChat(messages, messageOptions);

                if (res.success && res.data) {
                    // Collect updates in a batch

                    const updates = [];

                    // Process each selector and property in the AI response
                    for (let selector in res.data) {
                        if (res.data.hasOwnProperty(selector)) {
                            // Loop through all properties for the current selector
                            for (let property in res.data[selector]) {
                                if (res.data[selector].hasOwnProperty(property)) {
                                    const value = res.data[selector][property];
                                    // Determine unit if needed
                                    const unit = property.includes('color') ? '' : '';

                                    // Add to updates batch
                                    updates.push({
                                        selector: selector,
                                        property: property,
                                        value: value + unit
                                    });
                                }
                            }
                        }
                    }


                    // Apply all updates at once
                    if (updates.length > 0) {
                        this.$emit('batch-update', updates);
                    }

                    // Clear form input after successful request
                    this.aiMessage = '';


                } else {
                    throw new Error('Invalid response from AI');
                }
            } catch (error) {
                console.error('AI design change error:', error);
                this.error = 'Failed to change design with AI: ' + (error.message || 'Unknown error');
            } finally {
                // Always remove the spinner and reset loading state
                window.mw.top().spinner({element: window.mw.top().doc.body, size: 60, decorate: true}).remove();
                this.loading = false;
            }
        }
    },


    mounted() {

        this.supportedFonts = mw.top().app.fontManager.getFonts();

        // Update the UI based on AI availability
        if (this.isAIAvailable && document.querySelector('.ai-change-template-design-button')) {
            document.querySelector('.ai-change-template-design-button').classList.remove('d-none');
        }

        this.initAIChatForm()

        console.log('FieldAiChangeDesign mounted, AI availability:', this.isAIAvailable);


    },
    beforeUnmount() {
        // Clean up AIChatForm instance if exists
        if (this.aiChatFormInstance && typeof this.aiChatFormInstance.remove === 'function') {
            try {
                this.aiChatFormInstance.remove();
            } catch (e) {
                console.error('Failed to remove AIChatForm', e);
            }
            this.aiChatFormInstance = null;
        }
    }
};
</script>

<style scoped>
.position-relative {
    position: relative;
}

/* Modern AI Chat Text Field Styling */
:deep(.mw-ai-chat-box .mw-ai-chat-box-area-field) {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 14px;
    line-height: 1.5;
    color: #334155;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
    resize: vertical;
    min-height: 100px;
    width: 100%;
    outline: none;
    position: relative;
}

:deep(.mw-ai-chat-box .mw-ai-chat-box-area-field:focus) {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
}

:deep(.mw-ai-chat-box .mw-ai-chat-box-area-field::placeholder) {
    color: #64748b;
    font-style: italic;
    opacity: 0.7;
}

:deep(.mw-ai-chat-box .mw-ai-chat-box-area-field:hover) {
    border-color: #64748b;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

/* AI Chat Box Container */
:deep(.mw-ai-chat-box) {
    position: relative;
    margin-bottom: 16px;
}

:deep(.mw-ai-chat-box::before) {
    content: '✨ AI Assistant';
    position: absolute;
    top: -8px;
    left: 16px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 8px;
    letter-spacing: 0.5px;
    z-index: 10;
    text-transform: uppercase;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

/* Submit Button Styling */
:deep(.mw-ai-chat-box .mw-ai-chat-box-submit-btn) {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 24px;
    color: white;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    position: relative;
    overflow: hidden;
}

:deep(.mw-ai-chat-box .mw-ai-chat-box-submit-btn:hover) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
}

:deep(.mw-ai-chat-box .mw-ai-chat-box-submit-btn:active) {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
}

:deep(.mw-ai-chat-box .mw-ai-chat-box-submit-btn::before) {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

:deep(.mw-ai-chat-box .mw-ai-chat-box-submit-btn:hover::before) {
    left: 100%;
}

/* Loading and Error States */
.text-center {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #6366f1;
    font-weight: 500;
}

.text-danger {
    color: #ef4444;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
    :deep(.mw-ai-chat-box .mw-ai-chat-box-area-field) {
        padding: 14px 16px;
        font-size: 16px; /* Prevents zoom on iOS */
        min-height: 80px;
    }

    :deep(.mw-ai-chat-box .mw-ai-chat-box-submit-btn) {
        width: 100%;
        padding: 14px 24px;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    :deep(.mw-ai-chat-box .mw-ai-chat-box-area-field) {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        border-color: #475569;
        color: #e2e8f0;
    }

    :deep(.mw-ai-chat-box .mw-ai-chat-box-area-field::placeholder) {
        color: #94a3b8;
    }

    :deep(.mw-ai-chat-box .mw-ai-chat-box-area-field:focus) {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-color: #3b82f6;
    }
}
</style>
