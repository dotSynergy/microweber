<template>
    <div>
        <div class="text-shadow-options">
            <!-- Predefined Shadows -->
            <PredefinedTextShadowsSelect :predefinedShadows="predefinedShadows"
                                         :selectedShadow="selectedShadow"
                                         @update:selectedShadow="handleShadowChange"/>

            <!-- Customization Options -->
            <div v-if="selectedShadow === 'custom'" class="form-group">
                <SliderSmall label="Horizontal Shadow Length" v-model="textShadowOptions.horizontalLength" :min="-300"
                             :max="300" :step="1" :default="0"></SliderSmall>
                <SliderSmall label="Vertical Shadow Length" v-model="textShadowOptions.verticalLength" :min="-300"
                             :max="300" :step="1" :default="0"></SliderSmall>
                <SliderSmall label="Blur Radius" v-model="textShadowOptions.blurRadius" :min="0" :max="30"
                             :step="1" :default="0"></SliderSmall>
                <ColorPicker v-model="textShadowOptions.shadowColor" :color="textShadowOptions.shadowColor"
                             :label="'Color'" @change="handleTextShadowColorChange"/>
            </div>
        </div>
    </div>
</template>

<script>
import ColorPicker from "./components/ColorPicker.vue";
import SliderSmall from "./components/SliderSmall.vue";
import PredefinedTextShadowsSelect from "./components/PredefinedTextShadowsSelect.vue";

export default {
    components: {ColorPicker, SliderSmall, PredefinedTextShadowsSelect},

    data() {
        var predefinedShadows = mw.top().app.templateSettings.getPredefinedTextShadows();

        return {
            activeNode: null,
            isReady: false,
            selectedShadow: '',
            predefinedShadows: predefinedShadows,
            textShadowOptions: {
                horizontalLength: "",
                verticalLength: "",
                blurRadius: "",
                shadowColor: "",
            },
        };
    },

    mounted() {
        this.emitter.on("element-style-editor-show", () => {
            if (this.$root.selectedElement) {
                this.populateStyleEditor(this.$root.selectedElement);
            }
        });

        this.emitter.on("element-style-editor-show", elementStyleEditorShow => {
            if (elementStyleEditorShow !== 'showTextShadowOptions') {
                this.showTextShadowOptions = false;
            } else {
                this.showTextShadowOptions = true;
            }
        });
    },

    watch: {
        '$root.selectedElement': {
            handler: function (element) {
                if (element) {
                    this.populateStyleEditor(element);
                }
            },
            deep: true
        },
        textShadowOptions: {
            handler: function (newVal, oldVal) {
                if (this.selectedShadow === 'custom') {
                    this.applyTextShadow();
                }
            },
            deep: true,
        },
    },

    methods: {
        applyPropertyToActiveNode: function (prop, val) {
            if (!this.isReady) {
                return;
            }

            if (this.activeNode) {
                this.$root.applyPropertyToActiveNode(this.activeNode, prop, val);
            }
        },

        handleTextShadowColorChange(color) {
            if (typeof color !== "string") {
                return;
            }
            this.textShadowOptions.shadowColor = color;
        },

        handleShadowChange(selectedShadow) {
            if (!this.isReady) {
                return;
            }

            this.selectedShadow = selectedShadow;

            if (this.selectedShadow === '') {
                this.resetAllProperties();
                this.applyPropertyToActiveNode("textShadow", "");
                return;
            }

            if (this.selectedShadow === 'custom') {
                // Parse current shadow if exists to populate custom options
                if (this.activeNode) {
                    var textShadowVal = getComputedStyle(this.activeNode).getPropertyValue('text-shadow');
                    if (textShadowVal && textShadowVal !== 'none') {
                        this.parseTextShadowValues(textShadowVal);
                    }
                }
                return;
            }

            this.applyPropertyToActiveNode("textShadow", this.selectedShadow);
        },

        resetAllProperties: function () {
            this.textShadowOptions = {
                horizontalLength: "",
                verticalLength: "",
                blurRadius: "",
                shadowColor: "",
            };
        },

        populateStyleEditor: function (node) {
            if (node && node.nodeType === 1) {
                this.isReady = false;
                this.resetAllProperties();
                this.activeNode = node;

                this.populateCssTextShadow();

                setTimeout(() => {
                    this.isReady = true;
                }, 100);
            }
        },

        populateCssTextShadow: function () {
            if (!this.activeNode || !this.activeNode.style) return;

            var textShadowVal = getComputedStyle(this.activeNode);
            textShadowVal = textShadowVal.getPropertyValue('text-shadow');

            if (textShadowVal === '' || textShadowVal === 'none' || textShadowVal === 'initial' || textShadowVal === 'unset' || textShadowVal === 'inherit') {
                this.selectedShadow = '';
                return;
            } else {
                if (this.predefinedShadows.some(shadow => shadow.value === textShadowVal)) {
                    this.selectedShadow = textShadowVal;
                } else {
                    this.selectedShadow = 'custom';
                    this.parseTextShadowValues(textShadowVal);
                }
            }
        },

        parseTextShadowValues(shadowString) {
            // Parse text shadow string to extract individual values
            var parts = shadowString.trim().split(/\s+/);

            if (parts.length >= 3) {
                this.textShadowOptions.horizontalLength = parts[0].replace('px', '');
                this.textShadowOptions.verticalLength = parts[1].replace('px', '');
                this.textShadowOptions.blurRadius = parts[2].replace('px', '');

                // Extract color (can be rgb, rgba, hex, or named color)
                var colorMatch = shadowString.match(/(rgb\([^)]+\)|rgba\([^)]+\)|#[a-fA-F0-9]{3,6}|[a-zA-Z]+)/);
                if (colorMatch) {
                    this.textShadowOptions.shadowColor = colorMatch[0];
                }
            }
        },

        applyTextShadow() {
            if (!this.isReady) {
                return;
            }

            const {
                horizontalLength,
                verticalLength,
                blurRadius,
                shadowColor,
            } = this.textShadowOptions;

            const textShadowValue = `${horizontalLength || 0}px ${verticalLength || 0}px ${blurRadius || 0}px ${shadowColor || 'rgba(0,0,0,0.2)'}`;
            this.applyPropertyToActiveNode("textShadow", textShadowValue);
        },
    },
};
</script>
