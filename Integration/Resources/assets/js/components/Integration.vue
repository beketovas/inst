<script>
import Preloader from '../../../../../../resources/js/components/Preloader.vue'
import Node from './Node.vue'
import {library} from '@fortawesome/fontawesome-svg-core'
import {faSync} from '@fortawesome/free-solid-svg-icons'
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import {Auth} from '@/js/modules/auth'

library.add(faSync);

export default {
    provide() {
        const IntegrationComponent = {};
        Object.defineProperty(IntegrationComponent, 'showErrors', {
            enumerable: true,
            get: () => this.showErrors,
        });
        Object.defineProperty(IntegrationComponent, 'readyForActivation', {
            enumerable: true,
            get: () => this.readyForActivation,
        });
        Object.defineProperty(IntegrationComponent, 'showAppTest', {
            enumerable: true,
            get: () => this.showAppTest,
        });
        Object.defineProperty(IntegrationComponent, 'integrationTestErrors', {
            enumerable: true,
            get: () => this.integrationTestErrors,
        });
        return {IntegrationComponent}
    },
    props: ['integrationId', 'isAdmin','integrationProp'],
    data() {
        return {
            integrationName: '',
            integration: null,
            integrationError: null,
            showErrors: false,
            loading: false,
            readyForActivation: false,
            showAppTest: false,
            userOnThisPage: false, // is user on this page in browser window or not
            triggerNode: null,
            actionNode: null,
            triggerNodeErrors: null,
            actionNodeErrors: null,
            integrationTestErrors: false
        }
    },
    methods: {
        changeName(event) {
            let el = event.target;
            $(el).removeClass('active');
            $(el).parents('h2').find('.change-name-input').addClass('active').focus();
        },
        saveIntegrationName(event) {
            let el = event.target;
            $(el).removeClass('active');
            $(el).parents('h2').find('.change-name-btn').addClass('active');

            axios.put('/api/integrations/' + this.integrationId + '/save-name', {
                name: el.value
            })
                .then((response) => {
                    this.integration = response.data.integration;

                })
                .catch((error) => {
                    //console.log(error);
                })
        },
        ClearNode(nodeId) {
            axios.put(`/api/integrations/${this.integration.code}/nodes/${nodeId}/clear`)
                .then(res => {
                    this.triggerNode = res.data.triggerNode;
                    this.actionNode = res.data.actionNode;
                    this.integration = res.data.integration;
                    this.integrationName = res.data.integrationName;
                })
        },
        read() {
            this.loading = true;
            axios.get('/api/integrations/' + this.integrationId + '/nodes-data')
                .then((res) => {
                    this.triggerNode = res.data.triggerNode;
                    this.actionNode = res.data.actionNode;

                    this.integration = res.data.integration;
                    this.loading = false;
                    this.integrationName = res.data.integrationName;

                    this.showErrors = false;
                    this.showAppTest = false;

                    $('.plus').removeClass('active');
                    $('.dropdown').removeClass('opened');
                    $('.select-dropdown').removeClass('active');

                })
                .catch((err) => {
                    this.loading = false;
                })
        },
        validateNodes() {
            this.loading = true;
            axios.get('/api/integrations/' + this.integrationId + '/nodes/validate')
                .then((res) => {
                    this.showErrors = true;
                    this.triggerNodeErrors = res.data.triggerNodeErrors;
                    this.actionNodeErrors = res.data.actionNodeErrors;
                    this.integrationTestErrors = res.data.integrationTestErrors;

                    this.loading = false;
                    this.readyForActivation = res.data.readyForActivation;
                })
                .catch((err) => {
                    this.loading = false;
                })
        },
        activateIntegration() {
            let vm = this;

            vm.$eventBus.$emit('loadingStart');
            axios.post('/api/integrations/' + this.integration.code + '/activate', {
                integration_id: vm.integration.id,
            })
                .then((response) => {
                    if (typeof response.data.error !== 'undefined') {
                        this.integrationError = response.data.error;
                    }
                    window.location.reload();
                })
                .catch((error) => {
                    this.$eventBus.$emit('loadingStop');
                })
        },
        findInObjectById(id, source) {
            let res = {};
            $.each(source, function (i, obj) {
                if (obj.id === id) {
                    res = obj;
                }
            });
            return res;
        },
        findKeyInObjectById(id, source) {
            let res = null;
            $.each(source, function (i, obj) {
                if (obj.id === id) {
                    res = i;
                }
            });
            return res;
        },
        hasErrors() {
            let errors = false;
            $.each(this.nodes, function (i, obj) {
                if (Object.keys(obj.errors).length) {
                    errors = true;
                }
            });
            return errors;
        },
    },
    components: {
        'font-awesome-icon': FontAwesomeIcon,
        Preloader,
        Node,
        //ActionTextField
    },
    created() {
        this.integration= this.$props.integrationProp
        let vm = this;
        this.$eventBus.$on('reloadNode', function () {
            vm.read();
        });
        $(document).ready(function () {
            //console.log('here');
            vm.userOnThisPage = true;
            vm.read();
        });
    },
    mounted() {
        let vm = this;


        this.$eventBus.$on('validateNodes', function () {
            vm.validateNodes();
        });

        this.$eventBus.$on('loadingStart', function () {
            vm.loading = true;
        });

        this.$eventBus.$on('loadingStop', function () {
            vm.loading = false;
        });

        this.$eventBus.$on('showErrors', function (showErrors) {
            vm.showErrors = showErrors;
        });

        this.$eventBus.$on('readyForActivation', function (readyForActivation) {
            vm.readyForActivation = readyForActivation;
        });

        this.$eventBus.$on('showAppTest', function (showAppTest) {
            vm.showAppTest = showAppTest;
        });

        this.$eventBus.$on('activateIntegration', function (integrationId) {
            vm.activateIntegration(integrationId);
        });

        // Check if user returned back on the window

        /*$(window).focus(function() {
            if(!vm.userOnThisPage) {
                //console.log('returned back');
                vm.userOnThisPage = true;
                vm.read();
            }
        });*/
        $(window).blur(function () {
            //console.log('moved away');
            vm.userOnThisPage = false;
        });

        axios.interceptors.response.use(
            function (response) {
                if (response.data.alreadyActivated === true || response.data.applicationHasBeenChanged === true || response.data.criticalError === true)
                    window.location.reload();
                return response;
            },
            function (error) {
                // handle error
                if (error.response) {
                    let response = error.response;
                    let status = response.status;
                    if (status === 404) {
                        window.location.href = vm.$helper.url('/app/ways')
                    } /*else if (status === 401) {
                        Auth.logout('/login')
                    }*/
                }
                return Promise.reject(error);
            });

    }
}
</script>
