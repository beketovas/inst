<template>
    <div class="fields-block">
        <node-field-refresh :node="node" :errors="errors"></node-field-refresh>
        <div class="fields-relative" v-if="fields">
            <div class="field-related-container" v-for="field in fields">
                <label>{{ field.title }}</label>
                <p v-if="field.description" v-html="field.description"></p>
                <div class="field-container">
                    <node-field-dropdown v-if="field.type === 'dropdown'"
                                         :field="field"
                                         :fields.sync="fields"
                                         :integration="integration"
                                         :nodeId="nodeId"
                                         :entity="entity"></node-field-dropdown>
                    <node-field-text :field="field" v-else-if="field.type === 'text'"></node-field-text>
                    <node-field-masked-string :field="field"
                                              v-else-if="field.type === 'masked_string'"></node-field-masked-string>
                    <node-field-boolean v-else-if="field.type === 'boolean'"
                                        :field="field"
                                        :integration="integration"
                                        :nodeId="nodeId"
                                        :entity="entity"></node-field-boolean>
                    <node-field-string :field.sync="field"
                                       :codemirrorEnabled="true"
                                       :relatedEnabled="true"
                                       v-else></node-field-string>
                </div>
                <span class="field-error-text"
                      v-if="IntegrationComponent.showErrors && !field.has_value && field.required">
                    {{ $t('validation.field_is_required') }}
                </span>
                <node-field-related-dropdown :field="field"></node-field-related-dropdown>
            </div>
        </div>
    </div>
</template>
<script>
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import {CodemirrorMarks} from '@/js/modules/codemirror/marks'

export default {
    inject: ['IntegrationComponent'],
    props: ['node', 'errors'],
    data() {
        return {
            entity: 'NodeField',
            applicationData: this.node.application_data,
            appNode: this.node.application_data.app_node,
            fields: this.node.application_data.fields,

            userId: this.node.user_id,
            nodeId: this.node.entity.id,
            integration: this.node.integration,
        }
    },
    watch: {
        node() {
            this.applicationData = this.node.application_data;
            this.appNode = this.node.application_data.app_node;
            this.fields = this.node.application_data.fields;
            this.integration = this.node.integration;
        }
    },
    methods: {
        codemirrorChanged(cm, event, field) {
            // Prevent if integration is active
            if (this.integration.active) return false;

            let vm = this,
                value = cm.getValue();

            let cmf = new CodemirrorMarks(cm);
            let marks = cmf.build();

            // If value was not changed
            if (field.value === value)
                return false;

            // If empty field was not changed
            if (field.value === null && value === '')
                return false;

            axios.post('/api/integrations/' + this.integration.code + '/nodes/' + this.nodeId + '/fields/store-value', {
                field_id: field.id,
                value: value,
                marks: marks,
                entity: vm.entity
            })
                .then((response) => {
                    this.$eventBus.$emit('showErrors', false);
                    field.value = value;
                    field.has_value = response.data.hasValue;
                })
                .catch((error) => {

                })
        },
        showAvailableFields(event, field) {
            // Prevent if integration is active
            if (this.integration.active) return false;

            let $clickedEl = $(event.target);
            if ($clickedEl.hasClass('active')) {
                $clickedEl.removeClass('active');
                $clickedEl.parents('.field-related-container').find('.dropdown').removeClass('opened');
                $clickedEl.parents('.field-related-container').find('.select_selected').removeClass('active');
            } else {
                $(".dropdown").removeClass("opened");
                $(".select-dropdown").removeClass("active");
                $('.plus').removeClass('active');
                $('.select_selected').removeClass('active');

                if (!field.available_related_fields) {
                    this.$eventBus.$emit('loadingStart');
                    axios.post('/api/integrations/' + this.integration.code + '/nodes/' + this.nodeId + '/fields/available', {
                        fieldId: field.id
                    })
                        .then((res) => {
                            field.available_related_fields = res.data.fields;
                            this.$eventBus.$emit('loadingStop');

                        })
                        .catch((err) => {
                            this.$eventBus.$emit('loadingStop');
                        })
                }
                setTimeout(() => {
                    $clickedEl.addClass('active');
                    $clickedEl.parents('.field-related-container').find('.dropdown').addClass('opened');
                    $clickedEl.parents('.field-related-container').find('.select_selected').addClass('active');
                }, 100)

            }
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
    },
    mounted() {
        let vm = this;

        this.$eventBus.$on('fieldChanged', function (event, field, entity) {
            vm.fieldChanged(event, field, entity);
        });
    },
    components: {
        'font-awesome-icon': FontAwesomeIcon
    },
}
</script>
