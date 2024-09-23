<template>
    <div class="integration-content-wrap">
        <div class="integration-content">
            <form method="post" action="" class="flow-block-form">
                <h3>{{ node.name }}</h3>
                <node-application :node="node" :errors="errors"></node-application>
                <div class="application-node">
                    <node-action v-if="node.application && node.application_data.available_actions" :node="node" :errors="errors"></node-action>
                    <node-application-test v-if="node.application && !node.is_trigger" :node="node" :errors="errors"></node-application-test>
                    <node-integration-test v-if="node.application && !node.is_trigger" :node="node" :errors="errors"></node-integration-test>
                    <node-link-generated v-if="node.application && node.is_trigger && node.application_data.webhook_link" :node="node" :errors="errors"></node-link-generated>
                    <node-fields :node="node" :errors="errors" v-if="node.application"></node-fields>
                </div>
            </form>
        </div>

        <div class="circle-block" v-if="node.is_trigger">
            <a class="circle" href="https://apiway.crunch.help/" target="_blank">?</a>
            <span class="arrow"></span>
        </div>
    </div>
</template>
<script>
    export default {
        props: ['node', 'errors'],
        data() {
            return {
                integrationId: this.$parent.integrationId,
                integration: this.$parent.integration,
            }
        },
        computed: {
            currentApplicationNode: function () {
                if(this.node.application) {
                    return this.node.application.slug + '-node';
                }
                return '';
            }
        },
        methods: {
            read() {
                axios.get('/api/integrations/' + this.$parent.integrationId + '/nodes/' + this.nodeId + '/data')
                    .then((res) => {
                        //console.log("response after reading data for node");
                        //console.log(res);
                        this.node = res.data.node;
                    })
                    .catch((err) => {
                        //console.log(err);
                        this.loading = false;
                    })
            }
        },
        mounted() {

        }
    }
</script>
