<template>
    <div class="app-node-section" v-if="fields">
        <div class="field-container" v-for="field in fields">
            <label>{{ field.title }}</label>
            <p v-if="field.description" v-html="field.description"></p>
            <node-field-dropdown v-if="field.type === 'dropdown'"
                                 :field="field"
                                 :fields.sync="fields"
                                 :integration="integration"
                                 :nodeId="nodeId"
                                 :entity="entity"></node-field-dropdown>
            <node-field-text :field="field" v-else-if="field.type === 'text'"></node-field-text>
            <node-field-masked-string :field="field" v-else-if="field.type === 'masked_string'"></node-field-masked-string>
            <node-field-boolean v-else-if="field.type === 'boolean'"
                                :field="field"
                                :integration="integration"
                                :nodeId="nodeId"
                                :entity="entity" ></node-field-boolean>
            <node-field-string :field="field"
                               :codemirrorEnabled="false"
                               :relatedEnabled="false"
                               v-else-if="field.type === 'string'"></node-field-string>

            <span class="field-error-text" v-if="IntegrationComponent.showErrors && !field.has_value && field.required">
                {{ $t('validation.field_is_required') }}
            </span>
        </div>
    </div>
</template>
<script>
export default {
    inject: ['IntegrationComponent'],
    props: ['node', 'errors'],
    data() {
        return {
            entity: 'Field',
            fields: this.node.application_data.settings,
            nodeId: this.node.entity.id,
            integration: this.node.integration,
        }
    },
    watch: {
        node() {
            this.fields = this.node.application_data.settings;
            this.integration = this.node.integration
        }
    },
    methods: {

    },

}
</script>
