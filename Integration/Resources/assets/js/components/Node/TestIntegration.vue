<template>
    <div class="app-node-section">
        <div class="field-error-text" v-if="IntegrationComponent.showErrors && IntegrationComponent.integrationTestErrors">
            {{ $t('validation.check_errors_in_left_menu') }}
        </div>
        <div class="btns-block" v-if="!node.is_trigger && action && fields">
            <a class="main_button small blue_theme" @click.prevent="testIntegration()">{{ $t('node.test_integration') }}</a>
            <a :href="integrationsLink" @click.prevent="activateIntegration()" class="main_button small blue_theme" v-if="IntegrationComponent.readyForActivation">{{ $t('node.activate_integration') }}</a>
        </div>
    </div>
</template>
<script>

export default {
    inject: ['IntegrationComponent'],
    props: ['node', 'errors'],
    data() {
        return {
            action: this.node.application_data.action,
            fields: this.node.application_data.fields,
            integrationsLink: this.node.integrations_link,

            nodeId: this.node.entity.id,
            integration: this.node.integration,
        }
    },
    watch: {
        node() {
            this.action = this.node.application_data.action;
            this.fields = this.node.application_data.fields;
            this.integration = this.node.integration
        }
    },
    methods: {
        testIntegration() {
            // Prevent if integration is active
            if(this.integration.active) return false;

            this.$eventBus.$emit('validateNodes');
            this.$eventBus.$emit('showAppTest', true);
        },
        activateIntegration() {
            this.$eventBus.$emit('activateIntegration');
        }
    }
}
</script>

<style scoped>
    .field-error-text {
        margin-bottom: 10px;
    }
</style>
