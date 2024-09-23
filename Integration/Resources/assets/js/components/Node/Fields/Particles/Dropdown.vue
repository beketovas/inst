<template>
    <div class="select-container select">
        <div class="clear_button" v-if="!!field.value_json.label" @click.prevent="optionChanged($event, null)">
            Clear
        </div>
        <div class="select-dropdown select_selected main_input select-list" @click="loadFieldValues($event, field.id)">
            <span class="select-dropdown-text with-icon" v-if="field.value_json.label">
                {{ field.value_json.label }}
            </span>

            <span class="select-dropdown-text" v-else>
                {{ $t('node.select') }} {{ field.title }}
            </span>

            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" class="chevron">
                <g class="chevron__lines">
                    <path d="M10 50h40" class="chevron__line chevron__line_left"></path>
                    <path d="M90 50H50" class="chevron__line chevron__line_right"></path>
                </g>
            </svg>
        </div>
        <div class="dropdown list select_list">
            <ul>
                <li v-for="item in items" :class="{active: field.value_json && field.value_json.value === item.value}">
                    <a class="select_list__item" v-if="item.label" @click.prevent="optionChanged($event, item)"
                       href="#">{{ item.label }}</a>
                </li>
            </ul>
        </div>

        <span class="field-error-text" v-if="IntegrationComponent.showErrors && error">
           {{ error }}
        </span>
    </div>
</template>

<script>
import {Node} from '@modules/Integration/Resources/assets/js/modules/node'


export default {
    inject: ['IntegrationComponent'],
    props: ['field', 'fields', 'integration', "nodeId", "entity"],
    data() {
        return {
            items: null,
            error: null,
            parentValue: null
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
        this.parentValue = this.findParentValue()
    },
    watch: {
        field() {
            this.items = null;
            this.error = null;
        }
    },
    methods: {
        loadFieldValues(event, fieldId) {

            Scrollbar.init(event.currentTarget.parentNode.querySelector('.select_list'));
            let vm = this;
            if (this.items === null || this.parentValue !== this.findParentValue()) {
                this.$eventBus.$emit('loadingStart');
                axios.post('/api/integrations/' + this.integration.code + '/nodes/' + this.nodeId + '/load-dropdown-values', {
                    field_id: fieldId,
                    entity: vm.entity
                })
                    .then((res) => {
                        if (res.data.errorMessage) {
                            this.$eventBus.$emit('showErrors', true);
                            vm.error = res.data.errorMessage;
                        } else {
                            vm.items = res.data.items;
                            this.$eventBus.$emit('showErrors', false);
                            vm.error = null;
                        }
                        this.$eventBus.$emit('loadingStop');
                        this.parentValue = this.findParentValue()
                    })
                    .catch((err) => {
                        this.$eventBus.$emit('loadingStop');
                    })
            }
        },
        save(event, item) {
            let value = !!item ? item.value : '',
                label = !!item ? item.label : '',
                additional_data = !!item ? item.additional_data : null,
                vm = this;

            // If chosen the same value return false
            if (vm.field.value_json && vm.field.value_json.value === value)
                return false;

            axios.post('/api/integrations/' + this.integration.code + '/nodes/' + this.nodeId + '/store-dropdown-value', {
                field_id: vm.field.id,
                field_value: value,
                field_label: label,
                field_additional_data: additional_data,
                entity: vm.entity
            })
                .then((response) => {
                    vm.field.value_json.value = response.data.value;
                    vm.field.value_json.label = response.data.label;
                    vm.field.has_value = response.data.hasValue;

                    this.$eventBus.$emit('showErrors', false);

                    // Try to clear dependent fields
                    let node = new Node(vm.$parent.node)
                    if (node.clearDependent(vm.field, vm.entity)) {
                        this.$eventBus.$emit('reloadNode');
                    }

                    //If field has loader, it means that new fields will be loaded, so reload node
                    if (vm.field.loader) {
                        this.$eventBus.$emit('reloadNode');
                    }
                })
                .catch((error) => {
                })
        },
        optionChanged(event, item) {
            // Prevent if integration is active
            if (this.integration.active)
                return false;

            this.save(event, item);
        },
        findParentValue() {
            let this_parent_field_name = JSON.parse(this.$props.field.uses_fields)
            let parent

            if (!!this_parent_field_name && !!this.$props.fields.length) {
                this_parent_field_name.forEach(parentName => {
                    parent = this.$props.fields.find(item => {
                        return item.identifier === parentName
                    })
                })
            }

            return !!parent ? parent.value_json.value : null
        }
    },
    components: {},

}
</script>
