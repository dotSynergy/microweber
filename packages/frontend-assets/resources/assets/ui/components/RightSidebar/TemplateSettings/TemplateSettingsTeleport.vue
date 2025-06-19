<template>
    <Teleport defer :to="teleportTarget">


        <TemplateSettings :setting="setting"></TemplateSettings>

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
        },
        setting: {
            type: String,
            default: null,
            required: false
        }
    }
};
</script>
