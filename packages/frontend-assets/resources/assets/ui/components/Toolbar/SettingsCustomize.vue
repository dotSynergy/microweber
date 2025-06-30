<style>


.mw-live-edit-right-sidebar-wrapper.mw-live-edit-right-sidebar-template-sidebar .mw-admin-action-links svg {


    fill: currentColor !important;
    margin: 0 !important;
    margin-inline: 0 !important;

    width: 25px !important;
    max-width: none !important;
    height: auto !important;
    max-height: none !important;
}


.mw-live-edit-right-sidebar-wrapper.mw-live-edit-right-sidebar-template-sidebar .mw-admin-action-links,
.mw-live-edit-right-sidebar-wrapper.mw-live-edit-right-sidebar-template-sidebar .btn-icon.live-edit-toolbar-buttons {
    height: 39px;
    width: 39px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 !important;
    padding: 0 !important;
}


.mw-live-edit-right-sidebar-wrapper.mw-live-edit-right-sidebar-template-sidebar .mw-admin-action-links:after,
.mw-live-edit-right-sidebar-wrapper.mw-live-edit-right-sidebar-template-sidebar .mw-admin-action-links svg + span{
    display: none !important;
}
.mw-live-edit-right-sidebar-wrapper.mw-live-edit-right-sidebar-template-sidebar ul{
    display: flex;
    flex-direction: column;
    align-items: center !important;
    justify-content: start !important;
    width: 100%;
    gap: 22px !important;
}
.mw-live-edit-right-sidebar-wrapper.mw-live-edit-right-sidebar-template-sidebar{
    position: absolute;
    right: 0;
    bottom: 0;
    width: 50px;
    height: calc(100vh - var(--toolbar-height));
    transition: transform .4s,  right .4s;
    display: flex;
    flex-direction: column;
    align-items: center !important;
    justify-content: start !important;
    max-height: none !important;
    gap: 22px !important;
    padding: 10px 0 !important;
    margin: 0 !important;
    border-radius: 0 !important;
    z-index: 2;


}

.live-edit-gui-editor-opened .mw-live-edit-right-sidebar-wrapper.mw-live-edit-right-sidebar-template-sidebar{
    right: var(--sidebar-end-size);
}

</style>


<template>





    <div class="mw-live-edit-right-sidebar-wrapper me-2 " :class="'mw-live-edit-right-sidebar-template-' + template">



        <div v-on:click="handleQuickEdit()" :class="{'live-edit-right-sidebar-active': buttonIsActiveQuickEdit }"
            class="btn-icon live-edit-toolbar-buttons" title="Quick AI edit">
            <svg xmlns="http://www.w3.org/2000/svg" height="22px" viewBox="0 -960 960 960" width="22px" fill="currentColor"><path d="M323-160q-11 0-20.5-5.5T288-181l-78-139h58l40 80h92v-40h-68l-40-80H188l-57-100q-2-5-3.5-10t-1.5-10q0-4 5-20l57-100h104l40-80h68v-40h-92l-40 80h-58l78-139q5-10 14.5-15.5T323-800h97q17 0 28.5 11.5T460-760v160h-60l-40 40h100v120h-88l-40-80h-92l-40 40h108l40 80h112v200q0 17-11.5 28.5T420-160h-97Zm237 0q-33 0-56.5-23.5T480-240q0-23 11-40.5t29-28.5v-342q-18-11-29-28.5T480-720q0-33 23.5-56.5T560-800q33 0 56.5 23.5T640-720q0 23-11 40.5T600-651v101l80-48q0-34 23.5-58t56.5-24q33 0 56.5 23.5T840-600q0 33-23.5 56.5T760-520q-11 0-20.5-2.5T721-530l-91 55 101 80q7-3 14-4t15-1q33 0 56.5 23.5T840-320q0 33-23.5 56.5T760-240q-37 0-60.5-28T681-332l-81-65v89q18 11 28.5 28.5T639-240q0 33-23 56.5T560-160Z"/></svg>
        </div>






        <div v-on:click="toggle('style-editor')" :class="{'live-edit-right-sidebar-active': !buttonIsActive && buttonIsActiveStyleEditor }"
             class="btn-icon live-edit-toolbar-buttons live-edit-toolbar-button-css-editor-toggle" title="Design">
            <svg   fill="currentColor"
                 xmlns="http://www.w3.org/2000/svg" height="22"
                 viewBox="0 -960 960 960" width="22">
                <path
                    d="M480-120q-133 0-226.5-92T160-436q0-65 25-121.5T254-658l226-222 226 222q44 44 69 100.5T800-436q0 132-93.5 224T480-120ZM242-400h474q12-72-13.5-123T650-600L480-768 310-600q-27 26-53 77t-15 123Z"/>
            </svg>
        </div>


        <span v-on:click="toggle('template-settings')" :class="{'live-edit-right-sidebar-active': buttonIsActive && !buttonIsActiveStyleEditor }"
              class="btn-icon live-edit-toolbar-buttons live-edit-toolbar-button-css-editor-toggle" title="Template settings">


            <svg fill="currentColor" xmlns="http://www.w3.org/2000/svg" height="22" viewBox="0 96 960 960" width="22"><path
                d="M480 976q-82 0-155-31.5t-127.5-86Q143 804 111.5 731T80 576q0-83 32.5-156t88-127Q256 239 330 207.5T488 176q80 0 151 27.5t124.5 76q53.5 48.5 85 115T880 538q0 115-70 176.5T640 776h-74q-9 0-12.5 5t-3.5 11q0 12 15 34.5t15 51.5q0 50-27.5 74T480 976Zm0-400Zm-220 40q26 0 43-17t17-43q0-26-17-43t-43-17q-26 0-43 17t-17 43q0 26 17 43t43 17Zm120-160q26 0 43-17t17-43q0-26-17-43t-43-17q-26 0-43 17t-17 43q0 26 17 43t43 17Zm200 0q26 0 43-17t17-43q0-26-17-43t-43-17q-26 0-43 17t-17 43q0 26 17 43t43 17Zm120 160q26 0 43-17t17-43q0-26-17-43t-43-17q-26 0-43 17t-17 43q0 26 17 43t43 17ZM480 896q9 0 14.5-5t5.5-13q0-14-15-33t-15-57q0-42 29-67t71-25h70q66 0 113-38.5T800 538q0-121-92.5-201.5T488 256q-136 0-232 93t-96 227q0 133 93.5 226.5T480 896Z"/></svg>
        </span>



            <div class="mw-admin-action-links mw-adm-liveedit-tabs" v-on:click="handleLayers()" :title="$lang('Layers')" :class="{'live-edit-right-sidebar-active': !!layers }">
                <svg fill="currentColor" class="mb-1 me-2" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px">
                <path d="M480-400 40-640l440-240 440 240-440 240Zm0 160L63-467l84-46 333 182 333-182 84 46-417 227Zm0 160L63-307l84-46 333 182 333-182 84 46L480-80Zm0-411 273-149-273-149-273 149 273 149Zm0-149Z"/>
            </svg>
                <Lang>Layers</Lang>
            </div>


        <div v-if="!advanced" v-on:click="handleAdvanced()" :class="{'live-edit-right-sidebar-active': buttonIsActiveQuickEdit }"
            class="btn-icon live-edit-toolbar-buttons" :title="$lang('Advanced')">
           <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-320q17 0 28.5-11.5T520-360q0-17-11.5-28.5T480-400q-17 0-28.5 11.5T440-360q0 17 11.5 28.5T480-320Zm-40-120h80v-200h-80v200ZM370-80l-16-128q-13-5-24.5-12T307-235l-119 50L78-375l103-78q-1-7-1-13.5v-27q0-6.5 1-13.5L78-585l110-190 119 50q11-8 23-15t24-12l16-128h220l16 128q13 5 24.5 12t22.5 15l119-50 110 190-103 78q1 7 1 13.5v27q0 6.5-2 13.5l103 78-110 190-118-50q-11 8-23 15t-24 12L590-80H370Zm70-80h79l14-106q31-8 57.5-23.5T639-327l99 41 39-68-86-65q5-14 7-29.5t2-31.5q0-16-2-31.5t-7-29.5l86-65-39-68-99 42q-22-23-48.5-38.5T533-694l-13-106h-79l-14 106q-31 8-57.5 23.5T321-633l-99-41-39 68 86 64q-5 15-7 30t-2 32q0 16 2 31t7 30l-86 65 39 68 99-42q22 23 48.5 38.5T427-266l13 106Zm40-320Z"/></svg>

        </div>




        <div  v-if="template === 'sidebar' && advanced" style="width: 100%;">
            <ToolsButtons></ToolsButtons>
        </div>
        <div class="dropdown btn-icon live-edit-toolbar-buttons" v-if="template === 'default'">
            <a role="button" id="dropdownLiveEditMenuLinkMoreSettings" data-bs-toggle="dropdown" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24">
                    <path
                        d="M480-345 240-585l56-56 184 184 184-184 56 56-240 240Z"/>
                </svg>
            </a>

            <div class="dropdown-menu mw-live-edit-tools-dropdown-menu"
                 aria-labelledby="dropdownLiveEditMenuLinkMoreSettings" ref="moreSettingsDropdown">
                <ToolsButtons></ToolsButtons>
            </div>
        </div>


    </div>
</template>


<script>


import ToolsButtons from "../RightSidebar/ToolsButtons.vue";
import ToolbarMulilanguageSelector from "./ToolbarMulilanguageSelector.vue";
import CSSGUIService from "../../../api-core/services/services/css-gui.service.js";

let handleLayersChange = function(){
    this.layers = mw.top().app.liveEditWidgets.status.layersOpened;
};


export default {
    components: {
        ToolsButtons,
        ToolbarMulilanguageSelector,

    },
    props: {
        template: {
            type: String,
            default: 'default',
        },
    },
    methods: {
        handleQuickEdit: function () {
            mw.app.liveEditWidgets.toggleQuickEditComponent()
        },
        handleAdvanced () {
            this.advanced = true;
        },
        handleLayers() {

            mw.app.liveEditWidgets.toggleLayers();
        },
        show: function (name) {

            this.emitter.emit('live-edit-ui-show', name);
            this.$refs.moreSettingsDropdown?.classList.remove('show');
        },
        toggle: function (name) {


            this.$refs.moreSettingsDropdown?.classList.remove('show');



            if(name !== 'style-editor') {
                CSSGUIService.hide()
            }
            if(name === 'template-settings') {

                mw.top().app.templateSettingsWidget.toggle()








            } else if(name === 'style-editor') {

                CSSGUIService.toggle()


            }

        },

        hideMoreSettingsDropdown() {
            this.$refs.moreSettingsDropdown?.classList.remove('show');
        },

        openReportIssueModal() {

            var url = 'https://microweber.org/go/feedback/';
            let linkInModal = mw.top().dialogIframe({
                url: url,
                width: 900,
                height: 900,
                closeOnEscape: true,

            });
            linkInModal.dialogContainer.style.paddingLeft = '0px';
            linkInModal.dialogContainer.style.paddingRight = '0px';
            linkInModal.dialogFooter.style.display = 'none';


        }
    },

    unmounted() {

        mw.top().app.liveEditWidgets.off('layersOpen', handleLayersChange);
        mw.top().app.liveEditWidgets.off('layersClose', handleLayersChange);

    },
    mounted() {

        handleLayersChange = handleLayersChange.bind(this);

        if(mw.top().app.liveEditWidgets) {
            mw.top().app.liveEditWidgets.on('layersOpen', handleLayersChange);
            mw.top().app.liveEditWidgets.on('layersClose', handleLayersChange);
        }




        mw.top().app.on('mw.open-template-settings', () => {
            // close the hamburger
            if (document.getElementById('user-menu-wrapper')) {
                document.getElementById('user-menu-wrapper').classList.remove('active');
            }

            this.hideMoreSettingsDropdown();

            this.show('template-settings')
        });

        mw.top().app.on('mw.open-report-issue-modal', () => {
            this.openReportIssueModal();
        });

        mw.app.canvas.on('canvasDocumentClick', event => {
            this.hideMoreSettingsDropdown();

        });

        mw.app.canvas.on('liveEditCanvasBeforeUnload', event => {
            this.hideMoreSettingsDropdown();
        });


        mw.app.canvas.on('liveEditCanvasLoaded', () => {
            mw.app.editor.on('onModuleSettingsRequest', event => {
                this.hideMoreSettingsDropdown();
            });

            mw.app.editor.on('onLayoutSettingsRequest', event => {
                this.hideMoreSettingsDropdown();
            });
            mw.app.editor.on('insertLayoutRequest', event => {
                this.hideMoreSettingsDropdown();
            });
            mw.app.editor.on('insertModuleRequest', event => {
                this.hideMoreSettingsDropdown();
            });


            if(mw.top().app.liveEditWidgets) {
            mw.top().app.liveEditWidgets.on('layersOpen', handleLayersChange);
            mw.top().app.liveEditWidgets.on('layersClose', handleLayersChange);
        }


        });

        let instance = this;
        this.emitter.on("live-edit-ui-show", (show) => {


            if (show == 'template-settings') {
                instance.buttonIsActive = true;
                instance.buttonIsActiveStyleEditor = false;
            } else if (show == 'style-editor') {
                instance.buttonIsActive = false;
                instance.buttonIsActiveStyleEditor = true;
            }else if (show == 'template-settings-close') {
                instance.buttonIsActive = false;
                instance.buttonIsActiveStyleEditor = false;
            } else {
                instance.buttonIsActive = false;
                instance.buttonIsActiveStyleEditor = false;

            }



        });


        mw.top().app.templateSettingsWidget.on('show', () => {
            this.buttonIsActive = true;
            mw.top().app.liveEditWidgets.closeQuickEditComponent();
        });
        mw.top().app.templateSettingsWidget.on('hide', () => this.buttonIsActive = false);

        mw.top().app.guiEditorBox.on('show', () => {
            this.buttonIsActiveStyleEditor = true;
            mw.top().app.liveEditWidgets.closeQuickEditComponent()
        });
        mw.top().app.guiEditorBox.on('hide', () => this.buttonIsActiveStyleEditor = false);

        mw.top().app.readyPromise(()=>{
            mw.top().app.liveEditWidgets.on('openQuickEditComponent', () => {
                this.buttonIsActiveQuickEdit = true
            })

            mw.top().app.liveEditWidgets.on('closeQuickEditComponent', () => {
                this.buttonIsActiveQuickEdit = false
            })
        });
    },
    data() {
        return {
            buttonIsActive: false,
            buttonIsActiveStyleEditor: false,
            buttonIsActiveQuickEdit: false,
            advanced: false,
            layers: false,

        }
    }
}
</script>
