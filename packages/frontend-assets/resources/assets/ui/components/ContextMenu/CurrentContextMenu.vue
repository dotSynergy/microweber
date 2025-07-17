
<template>

    <div>
        <CurrentNodeTextEditButton></CurrentNodeTextEditButton>
        <CurrentNodeModuleEditButton></CurrentNodeModuleEditButton>
        <CurrentLayoutSettingsButtons></CurrentLayoutSettingsButtons>
    </div>
</template>


<script>


import CurrentLayoutSettingsButtons from "../ContextMenu/CurrentLayoutSettingsButtons.vue";
import CurrentNodeTextEditButton from "../ContextMenu/CurrentNodeTextEditButton.vue";
import CurrentNodeModuleEditButton from "../ContextMenu/CurrentNodeModuleEditButton.vue";


export default {
    components: {
        CurrentNodeTextEditButton,
        CurrentNodeModuleEditButton,
        CurrentLayoutSettingsButtons,

    },

}
</script>

