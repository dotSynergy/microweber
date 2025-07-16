<template>
    <Teleport :to="teleportTarget" defer>


        <CurrentLayoutSettingsButtons></CurrentLayoutSettingsButtons>

    </Teleport>
</template>


<script>

import CurrentLayoutSettingsButtons from './CurrentLayoutSettingsButtons.vue';

export default {
    components: {
        CurrentLayoutSettingsButtons,
    },
    props: {
        teleportTarget: {
            type: String,
            default: '#current-layout-settings-teleport-widget-content'
        }

    }
};
</script>
