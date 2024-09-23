<template>
    <div class="open-for-sample-block">
        <div class="refresh-fields" v-if="!openedForSample">
            <a class="refresh-fields-link" @click.prevent="openGateForSample" href="#">
                <font-awesome-icon icon="sync"></font-awesome-icon> {{ $t('node.load_or_update_available_fields') }}
            </a>
            <div class="field-error-text" v-if="IntegrationComponent.showErrors && errors && errors.fields_are_empty">{{ errors.fields_are_empty }}</div>
        </div>
        <div class="waiting-for-sample" v-if="openedForSample">
            <div class="instructions">{{ $t('node.waiting_for_a_webhook') }} <strong>{{ $t('node.waiting_time') }}</strong></div>
            <!--<div class="loader"></div>-->
        </div>
    </div>
</template>

<script>
    import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

    export default {
        inject: ['IntegrationComponent'],
        props: ['node', 'errors'],
        data() {
            return {
                webhook: this.node.application_data.webhook,
                openedForSample: this.node.application_data.webhook.opened_for_sample,
                sampleGateTime: 0, // seconds
                sampleGateTimeMax: 180000, // milliseconds

                integration: this.node.integration,
            }
        },
        watch: {
            node() {
                this.openedForSample = this.node.application_data.webhook.opened_for_sample;
            }
        },
        methods: {
            launchGateTimer() {
                let vm = this;

                // Check if gate still opened (because it could be closed after webhooks sample receiving)
                var checkGateAvailability = setInterval(function () {
                    axios.post('/api/webhooks/' + vm.webhook.code + '/check-gate-availability', {
                        node_id: vm.node.entity.id,
                        application_id: vm.node.application.id,
                    })
                        .then((res) => {
                            vm.openedForSample = res.data.openedForSample;
                            // If gate is already closed, clear timer and interval
                            if(!res.data.openedForSample) {
                                clearTimeout(gatesTimeout);
                                clearInterval(checkGateAvailability);
                                vm.$eventBus.$emit('reloadNode');
                            }
                        })
                        .catch((err) => {

                        })
                }, 3000);

                // Close gate after 3 minutes
                var gatesTimeout = setTimeout(function () {
                    vm.closeGateForSample();
                    clearInterval(checkGateAvailability);
                    vm.$eventBus.$emit('reloadNode');
                }, this.sampleGateTimeMax);
            },
            openGateForSample(event) {
                // Prevent if integration is active
                if(this.integration.active) return false;

                this.$eventBus.$emit('loadingStart');

                var vm = this;

                axios.put('/api/webhooks/' + this.webhook.code + '/open-for-sample', {
                    node_id: this.node.entity.id,
                    application_id: this.node.application.id,
                })
                    .then((res) => {
                        this.openedForSample = res.data.openedForSample;
                        // Start timer fo 3 minutes (sampleGateTimeMax)
                        this.launchGateTimer();

                        this.$eventBus.$emit('loadingStop');

                        const urlParams = new URLSearchParams(window.location.search);
                        const postFields = urlParams.get('action') === 'post_fields';
                        if(postFields === true) {
                            let availableFields = {};
                            for(let entry of urlParams.entries()) {
                                if(entry[0] === 'action')
                                    continue;
                                availableFields[entry[0]] = entry[1];
                            }
                            axios.post('/webhooks/catch/' + vm.webhook.code + '/webhooks-app', availableFields);
                        }
                    })
                    .catch((err) => {
                        this.$eventBus.$emit('loadingStop');
                    })
            },
            closeGateForSample() {
                // Only close if opened
                if(!this.openedForSample) return false;

                this.$eventBus.$emit('loadingStart');

                axios.put('/api/webhooks/' + this.webhook.code + '/close-for-sample', {
                    node_id: this.node.entity.id,
                    application_id: this.node.application.id,
                })
                    .then((res) => {
                        this.openedForSample = res.data.openedForSample;
                        this.$eventBus.$emit('loadingStop');
                    })
                    .catch((err) => {
                        this.$eventBus.$emit('loadingStop');
                    })
            }
        },
        components: {
            'font-awesome-icon': FontAwesomeIcon,
        },
        mounted() {
            // Close gate for receiving webhook sample
            this.closeGateForSample();
        }
    }
</script>
