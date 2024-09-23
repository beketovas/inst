<template>
    <div class="refresh-fields">
        <a class="refresh-fields-link" @click.prevent="refreshFields" href="#">
            <font-awesome-icon icon="sync"></font-awesome-icon>
            {{ $t('node.load_or_update_available_fields') }}
        </a>
        <div class="field-error-text" v-if="IntegrationComponent.showErrors && fieldsRefreshingError">
            {{ fieldsRefreshingError }}
        </div>
        <div class="field-error-text" v-if="IntegrationComponent.showErrors && errors && errors.fields_are_empty">
            {{ errors.fields_are_empty }}
        </div>
    </div>
</template>
<script>
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'

export default {
    inject: ['IntegrationComponent'],
    props: ['node', 'errors'],
    data() {
        return {
            fieldsRefreshingError: null,
            nodeId: this.node.entity.id,
            integration: this.node.integration,
        }
    },
    watch: {
        node() {
            this.fieldsRefreshingError = null;
            this.integration = this.node.integration
        }
    },
    methods: {
        refreshFields(event) {
            // Prevent if integration is active
            if (this.integration.active) return false;

            this.$eventBus.$emit('loadingStart');
            axios.post('/api/integrations/' + this.integration.code + '/nodes/' + this.nodeId + '/fields/refresh')
                .then((res) => {
                    if (res.data.errorMessage) {
                        this.$eventBus.$emit('showErrors', true);
                        this.fieldsRefreshingError = res.data.errorMessage;
                        this.$eventBus.$emit('loadingStop');
                    } else {
                        this.$eventBus.$emit('reloadNode');
                    }
                })
                .catch((err) => {
                    this.$eventBus.$emit('loadingStop');
                })
        },

    },
    components: {
        'font-awesome-icon': FontAwesomeIcon,
    },
}
</script>
