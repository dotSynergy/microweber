<template>
    <Teleport defer :to="teleportTarget">


        <TemplateSettings></TemplateSettings>

    </Teleport>
</template>




<script>

import TemplateSettings from './TemplateSettings.vue';

export default {
    components: {
        TemplateSettings,
    },
    props: {
        teleportTarget: {
            type: String,
            default: '#template-settings-teleport-widget-content'
        }
    }
};
</script>
