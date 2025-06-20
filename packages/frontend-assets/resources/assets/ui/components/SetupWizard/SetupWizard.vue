<template>    <div v-if="showModal" style="visibility: hidden; position: absolute; width: 1px; height: 1px;"></div>
    <div v-if="showModal" v-on:click="hideModal" class="mw-le-overlay active"></div>

    <Transition
        enter-active-class="animate__animated animate__zoomIn"
        leave-active-class="animate__animated animate__zoomOut"
    >
        <div v-if="showModal"
             class="mw-le-dialog-block mw-le-layouts-dialog mw-setup-wizard-modal active"
             style="animation-duration: .3s;"
        >            <!-- Close Button -->
            <button
                type="button"
                class="mw-le-dialog-close-btn"
                @click="hideModal"
                aria-label="Close"
                style="position:absolute;top:16px;right:16px;z-index:10;background:none;border:none;font-size:2rem;line-height:1;cursor:pointer;"
            >
                &times;
            </button>

            <!-- Wizard Header -->
            <div class="mw-setup-wizard-header">
                <h3 class="text-center mb-4">Setup Wizard</h3>

                <!-- Progress Steps -->
                <div class="mw-wizard-steps d-flex justify-content-center mb-4">
                    <div
                        v-for="(step, index) in steps"
                        :key="index"
                        class="mw-wizard-step d-flex align-items-center me-4"
                        :class="{
                            'active': currentStep === index,
                            'completed': currentStep > index
                        }"
                        @click="navigateToStep(index)"
                    >
                        <div class="step-number me-2">{{ index + 1 }}</div>
                        <div class="step-title">{{ step.title }}</div>
                        <div v-if="index < steps.length - 1" class="step-connector ms-4"></div>
                    </div>
                </div>
            </div>

            <!-- Wizard Content -->
            <div class="mw-setup-wizard-content p-4">                <!-- Site Info Step -->
                <div v-if="currentStep === 0" class="wizard-step-content">
                    <h4 class="mb-4">Site Information</h4>
                    <div class="p-4 border rounded bg-light text-center">
                        <p class="text-muted">Site information settings will go here</p>
                        <small>Placeholder for site title, description, company name, contact email, etc.</small>


                    </div>
                </div>

                <!-- Homepage Step -->
                <div v-if="currentStep === 1" class="wizard-step-content">
                    <h4 class="mb-4">Homepage</h4>
                    <div class="p-4 border rounded bg-light text-center">
                        <p class="text-muted">Homepage configuration will go here</p>
                        <small>Placeholder for homepage layout, template selection, hero content, etc.</small>
                    </div>
                </div>

                <!-- Pages Step -->
                <div v-if="currentStep === 2" class="wizard-step-content">
                    <h4 class="mb-4">Pages</h4>
                    <div class="p-4 border rounded bg-light text-center">
                        <p class="text-muted">Page management will go here</p>
                        <small>Placeholder for creating basic pages, navigation setup, page templates, etc.</small>
                    </div>
                </div>

                <!-- Colors Step -->
                <div v-if="currentStep === 3" class="wizard-step-content">
                    <h4 class="mb-4">Colors</h4>
                    <div class="p-4 border rounded bg-light text-center">
                        <p class="text-muted">Color customization will go here</p>
                        <small>Placeholder for primary, secondary, accent colors, color palette selection, etc.</small>


                        <TemplateSettings setting="predefined-colors/main"></TemplateSettings>


                        <TemplateSettings setting="predefined-styles/button-styles"></TemplateSettings>



                    </div>
                </div>

                <!-- Fonts Step -->
                <div v-if="currentStep === 4" class="wizard-step-content">
                    <h4 class="mb-4">Fonts</h4>
                    <div class="p-4 border rounded bg-light text-center">
                        <p class="text-muted">Font selection will go here</p>
                        <small>Placeholder for font families, weights, sizes, typography settings, etc.</small>


                        <TemplateSettings setting="predefined-styles/text-styles"></TemplateSettings>


                    </div>
                </div>

            </div>

            <!-- Wizard Navigation -->
            <div class="wizard-navigation d-flex justify-content-between p-3 border-top">
                <button
                    v-if="currentStep > 0"
                    @click="previousStep"
                    class="btn btn-outline-secondary"
                >
                    Previous
                </button>
                <div v-else></div>

                <button
                    v-if="currentStep < steps.length - 1"
                    @click="nextStep"
                    class="btn btn-primary"
                >
                    Next
                </button>
                <button
                    v-else
                    @click="completeWizard"
                    class="btn btn-success"
                >
                    Complete Setup
                </button>
            </div>
        </div>
    </Transition>

    <div v-if="showModal" v-on:click="hideModal" class="mw-le-dialog-close active"></div>
</template>

<style>
/* Setup Wizard Modal */
.mw-setup-wizard-modal {
    max-width: 1000px !important;
    max-height: 700px !important;
    width: 90% !important;
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    margin: 0 !important;
    display: flex;
    flex-direction: column;
}

/* Wizard Header */
.mw-setup-wizard-header {
    padding: 20px 20px 10px;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}

/* Wizard Steps */
.mw-wizard-steps {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.mw-wizard-step {
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.mw-wizard-step.disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

.step-number {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    transition: all 0.3s ease;
    margin-right: 10px;
}

.step-title {
    font-weight: 500;
    color: #6c757d;
    transition: all 0.3s ease;
}

.mw-wizard-step.active .step-number {
    background: #007bff;
    color: white;
}

.mw-wizard-step.active .step-title {
    color: #007bff;
    font-weight: 600;
}

.mw-wizard-step.completed .step-number {
    background: #28a745;
    color: white;
}

.mw-wizard-step.completed .step-title {
    color: #28a745;
}

.step-connector {
    width: 30px;
    height: 2px;
    background: #e9ecef;
    position: absolute;
    right: -35px;
    top: 50%;
    transform: translateY(-50%);
}

.mw-wizard-step.completed .step-connector {
    background: #28a745;
}

/* Wizard Content */
.mw-setup-wizard-content {
    flex: 1;
    overflow-y: auto;
    min-height: 400px;
    max-height: 500px;
}

.wizard-step-content {
    padding: 0;
}

/* Color Preview */
.color-preview {
    width: 40px;
    height: 40px;
    border-radius: 4px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.color-preview:hover {
    transform: scale(1.1);
}

/* Wizard Navigation */
.wizard-navigation {
    margin-top: auto;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

/* Font Section (adapted from original) */
.mw-setup-wizard-content .col-md-4 {
    border-right: 1px solid #eee;
    height: 400px;
    overflow-y: auto;
    padding: 15px;
}

.mw-setup-wizard-content .col-md-8 {
    height: 400px;
    overflow-y: auto;
    padding: 15px;
}

/* Form styling improvements */
.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #495057;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .mw-setup-wizard-modal {
        width: 95% !important;
        max-height: 85vh !important;
    }

    .mw-wizard-steps {
        gap: 1rem;
    }

    .step-connector {
        display: none;
    }

    .mw-wizard-step {
        flex-direction: column;
        text-align: center;
    }

    .step-number {
        margin-right: 0;
        margin-bottom: 5px;
    }
}

/* Legacy font modal support */
.mw-font-picker-modal-wrapper {
    height: 100%;
    overflow: hidden;
}

/* Compact pagination styles */
.pagination-sm .page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    min-width: 32px;
    text-align: center;
}
</style>

<script>
import TemplateSettings from "../RightSidebar/TemplateSettings/TemplateSettings.vue";
import * as api from "../../../api-core/services/services/preview.service.js";

export default {
    name: 'SetupWizard',
    components: {
        TemplateSettings
    },

    mounted() {
        const instance = this;        // Initialize setup wizard listener
        mw.app.on('showSetupWizard', (params) => {
            this.params = params;
            this.openModal();
        });

        mw.app.on('hideSetupWizard', () => {
            this.hideModal();
        });

        // Close on Escape
        document.addEventListener('keyup', function (evt) {
            if (evt.keyCode === 27) {
                instance.hideModal();
            }
        });
    },

    data() {
        return {
            showModal: false,
            currentStep: 0,
            steps: [
                {title: 'Site Info', key: 'siteInfo'},
                {title: 'Homepage', key: 'homepage'},
                {title: 'Pages', key: 'pages'},
                {title: 'Colors', key: 'colors'},
                {title: 'Fonts', key: 'fonts'}
            ],
            params: null
        }
    },

    methods: {

        pagePreviewToggle: () => {
            //toggle  class to the body 'wizard-preview'

            document.body.classList.toggle('wizard-preview');

            api.pagePreviewToggle()
        },
        // Wizard navigation methods
        nextStep() {
            if (this.currentStep < this.steps.length - 1) {
                this.currentStep++;
            }
        },

        previousStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
            }
        },

        navigateToStep(stepIndex) {
            // Allow navigation to any step
            if (stepIndex >= 0 && stepIndex < this.steps.length) {
                this.currentStep = stepIndex;
            }
        },        completeWizard() {
            // Emit completion event
            mw.app.trigger('setupWizardComplete', {
                step: 'completed'
            });

            this.hideModal();
        },

        openModal() {
            this.showModal = true;
            this.currentStep = 0;
            this.pagePreviewToggle();
        },

        hideModal() {
            this.showModal = false;
            this.params = null;
            this.pagePreviewToggle();
        }
    }
}
</script>
