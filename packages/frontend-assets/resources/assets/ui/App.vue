<template>


    <div>
        <Toolbar></Toolbar>
        <!-- <LeftSidebar></LeftSidebar>-->
        <RightSidebar></RightSidebar>
        <ListLayouts></ListLayouts>
        <ListModules></ListModules>
       <ElementSettingsModal></ElementSettingsModal>
       <FontsManager></FontsManager>
       <SetupWizard></SetupWizard>
<!--       <ModalsContainer ></ModalsContainer >-->

    </div>

</template>



<script>
if(!mw.top) {
    mw.top = () => window.top.mw;
}


import { ModalsContainer } from 'vue-final-modal';
import Toolbar from "./components/Toolbar/Toolbar.vue";
import LeftSidebar from "./components/LeftSidebar/LeftSidebar.vue";
import RightSidebar from "./components/RightSidebar/RightSidebar.vue";
import ListLayouts from "./components/Layouts/ListLayouts.vue";
import ListModules from "./components/Modules/ListModules.vue";
import FontsManager from "./components/FontsManager/FontsManager.vue";
import ElementSettingsModal from "./components/ElementSettings/ElementSettingsModal.vue";
import SetupWizard from "./components/SetupWizard/SetupWizard.vue";

export default {
    components: {
        ElementSettingsModal,
        ModalsContainer,
        Toolbar,
        LeftSidebar,
        RightSidebar,
        ListLayouts,
        ListModules,
        FontsManager,
        SetupWizard,
    },

    data() {
        return {

        }
    },
    mounted() {

    }
}
</script>
