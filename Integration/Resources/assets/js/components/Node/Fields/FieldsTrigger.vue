<template>
    <div class="app-node-section">
        <div class="choosing-block">
            <div class="fields-block" >
                <node-field-refresh :node="node" :errors="errors" v-if="!node.application_data.webhook"></node-field-refresh>
                <node-field-trigger-open-for-sample :node="node" :errors="errors" v-if="node.application_data.webhook"></node-field-trigger-open-for-sample>
                <node-field-trigger-list :node="node" :errors="errors"></node-field-trigger-list>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: ['node', 'errors'],
    }
</script>
