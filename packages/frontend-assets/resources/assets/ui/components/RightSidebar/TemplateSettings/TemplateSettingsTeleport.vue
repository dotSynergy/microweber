<template>
    <Teleport defer to="#template-settings-teleport-widget-content">


        <TemplateSettings></TemplateSettings>

    </Teleport>
</template>

<style>
.settings-main-group {
    cursor: pointer;
}
</style>


<script>

import TemplateSettings from './TemplateSettings.vue';

export default {
    components: {
        TemplateSettings,

    }


};
</script>
