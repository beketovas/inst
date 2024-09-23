<template>
    <div class="fields-trigger" >
        <div v-if="fields">
            <div class="field-box main_input" v-for="field in fields">
                {{ field.title }}
            </div>
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
                fields: this.node.application_data.fields,
            }
        },
        watch: {
            node() {
                this.fields = this.node.application_data.fields;
            }
        },
        methods: {

        },
        components: {
            'font-awesome-icon': FontAwesomeIcon,
        },
    }
</script>
