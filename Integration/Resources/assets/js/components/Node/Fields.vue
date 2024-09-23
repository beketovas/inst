<template>
    <div class="fields-general-section">
        <div class="app-node-section" v-if="node.is_trigger && action && fields && fields.length">
            <div class="section-title">
                <h3>{{ $t('node.additional_settings') }}</h3>
            </div>
            <div class="choosing-block">
                <div class="settings-block">
                    <node-fields-settings :node="node" :errors="errors"></node-fields-settings>
                </div>
            </div>
        </div>
        <div class="app-node-section">
            <div class="section-title">
                <h3>{{ $t('node.transmitting_data') }}</h3>
            </div>
            <div class="choosing-block">
                <div class="fields-block">
                    <node-fields-trigger :node="node" :errors="errors" v-if="node.is_trigger"></node-fields-trigger>
                    <node-fields-action :node="node" :errors="errors" v-if="!node.is_trigger"></node-fields-action>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

export default {
    props: ['node', 'errors'],
    data() {
        return {
            appNode: this.node.application_data.app_node,
            action: this.node.application_data.action,
            fields: this.node.application_data.settings,

            userId: this.node.user_id,
            nodeId: this.node.entity.id,
            integration: this.node.integration,
        }
    },
    watch: {
        node() {
            this.appNode = this.node.application_data.app_node;
            this.action = this.node.application_data.action;
            this.fields = this.node.application_data.settings;
            this.integration = this.node.integration;
        }
    },
    methods: {},
    mounted() {

    }
}
</script>
