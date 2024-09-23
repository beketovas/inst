<template>
    <div class="select-container select">
        <div class="clear_button" v-if="!!field.value" @click.prevent="optionChanged($event, '')">
            Clear
        </div>
        <div class="select-dropdown select_selected main_input select-list">
            <span class="select-dropdown-text with-icon" v-if="field.value !== null">
                {{ $t(`field.boolean_label[${field.value}]`) }}
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
        <ul class="dropdown list select_list">
            <li  :class="{active: field.value && field.value === 0}">
                <a  class="select_list__item" @click.prevent="optionChanged($event, 0)" href="#">{{ $t('field.boolean_label.0') }}</a>
            </li>
            <li :class="{active: field.value && field.value === 1}">
                <a class="select_list__item" @click.prevent="optionChanged($event, 1)" href="#">{{ $t('field.boolean_label.1') }}</a>
            </li>
        </ul>
        <span class="field-error-text" v-if="IntegrationComponent.showErrors && error">
           {{ error }}
        </span>
    </div>
</template>

<script>
export default {
    inject: ['IntegrationComponent'],
    props: ['field', 'integration', "nodeId", "entity"],
    data() {
        return {
            error: null,
        }
    },
    watch: {
        field() {
            this.error = null;
        }
    },
    methods: {
        save(event, value) {
            let vm = this;

            // If chosen the same value return false
            if(vm.field.value === value)
                return false;

            axios.post('/api/integrations/' + this.integration.code + '/nodes/' + this.nodeId + '/fields/store-boolean', {
                field_id: vm.field.id,
                value: value,
                entity: vm.entity
            })
                .then((response) => {
                    vm.field.value = response.data.value;
                    vm.field.has_value = response.data.hasValue;

                    this.$eventBus.$emit('showErrors', false);
                })
                .catch((error) => {
                })
        },
        optionChanged(event, value) {
            // Prevent if integration is active
            if(this.integration.active)
                return false;

            this.save(event, value);
        },
    },
    components: {
    },
    mounted() {
    }
}
</script>
