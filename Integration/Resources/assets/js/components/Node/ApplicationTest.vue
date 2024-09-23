<template>
    <div class="app-node-section" v-if="node.application && IntegrationComponent.showAppTest">
        <div class="choosing-block">
            <div class="selected-application input-text"
                 :class="{correct: errors && !errors.application, invalid: errors && errors.application}">
                {{ node.application.name }}&nbsp;
                <span v-if="errors && errors.application">{{ serviceNotConnectedText }}</span>
                <span v-else>{{ serviceConnectedText }}</span>
            </div>
            <div class="field-error-text" v-if="IntegrationComponent.showErrors && errors && errors.application">{{ errors.application }}</div>
        </div>
    </div>
</template>
<script>

export default {
    inject: ['IntegrationComponent'],
    props: ['node', 'errors'],
    data() {
        return {
            serviceConnectedText: this.node.application_data.service_connected_text,
            serviceNotConnectedText: this.node.application_data.service_not_connected_text,
        }
    },
    watch: {
        node() {

        }
    },
    methods: {

    },
    mounted() {

    }
}
</script>
