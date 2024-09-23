<template>
    <div class="app-node-section">
        <div class="choosing-block">
            <div class="select-container select">
                <div class="select-dropdown select_selected main_input select-actions">
                    <span class="select-dropdown-text" v-if="action">
                        {{ action.name }}
                    </span>
                    <span class="select-dropdown-text" v-else>
                        {{ availableActionsSelectText }}
                    </span>
                    <div  class="clear_button" v-if="action" @click="clearInput">
                        Clear
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" class="chevron">
                        <g class="chevron__lines">
                            <path d="M10 50h40" class="chevron__line chevron__line_left"></path>
                            <path d="M90 50H50" class="chevron__line chevron__line_right"></path>
                        </g>
                    </svg>
                </div>

                <div class="dropdown list select_list">
                    <ul tabindex="1">
                        <li class="select_list__item" v-for="nodeAction in availableActions" :data-id="nodeAction.id"
                            @click.prevent="actionChanged" :class="{active: action && action.id == nodeAction.id}">
                            <a :data-id="nodeAction.id" href="#">{{ nodeAction.name }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <span class="field-error-text"
              v-if="IntegrationComponent.showErrors && errors && errors.application && node.is_trigger">
            {{ $t('validation.check_application_settings') }}
        </span>
    </div>
</template>
<script>

export default {
    inject: ['IntegrationComponent'],
    props: ['node', 'errors'],
    data() {
        return {
            account: this.node.application_data.account,
            action: this.node.application_data.action,
            instructionsPageLink: 'https://apiway.crunch.help/',
            availableActions: this.node.application_data.available_actions,
            availableActionsSelectText: 'Choose action',

            userId: this.node.user_id,
            nodeId: this.node.entity.id,
            integration: this.node.integration,
        }
    },
    mounted() {
        setTimeout(() => {
            document.querySelectorAll('.select_list').forEach(item => {
                Scrollbar.init(item, {
                    alwaysShowTracks: true
                })
            })
        }, 500)
    },
    watch: {
        node() {
            this.account = this.node.application_data.account;
            this.action = this.node.application_data.action;
            this.availableActions = this.node.application_data.available_actions;
            this.integration = this.node.integration
        }
    },
    methods: {
        clearInput(){
            if (this.integration.active)
                return false;
           this.actionChanged(null)
        },
        actionChanged(event) {
            let actionId = !!event? event.target.getAttribute('data-id') : null;

            // Prevent choosing the same action
            if (this.action && actionId === this.action.id) return false;
            this.$eventBus.$emit('loadingStart');
            axios.put('/api/integrations/' + this.integration.code + '/nodes/' + this.nodeId + '/' + this.node.application.slug + '/save-action', {
                action_id: actionId
            })
                .then((response) => {
                    this.$eventBus.$emit('reloadNode');
                })
                .catch((error) => {
                    console.log(error);
                })

        },
    }
}
</script>
