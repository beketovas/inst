<template>
    <div class="text-field-container">
        <masked-input type="text" class="main_input" v-model="fieldValue" :mask="field.mask" :placeholder="field.placeholder" name="task_deadline" @blur.native="fieldChanged" />
    </div>
</template>

<script>
    import MaskedInput from 'vue-masked-input';

    export default {
        props: ['field'],
        data() {
            return {
                fieldValue: this.field.value
            }
        },
        watch: {
            field() {
                this.fieldValue =  this.field.value
            }
        },
        methods: {
            fieldChanged(event) {
                // Prevent if integration is active
                if(this.$parent.integration.active) return false;

                let vm = this,
                    value = event.target.value;

                // If value was not changed
                if(vm.field.value === value)
                    return false;

                // If empty field was not changed
                if(vm.field.value === null && value === '')
                    return false;

                axios.post('/api/integrations/' + this.$parent.integration.code + '/nodes/' + this.$parent.nodeId + '/fields/store-value', {
                    field_id: vm.field.id,
                    value: value,
                    entity: vm.$parent.entity
                })
                    .then((response) => {
                        this.$eventBus.$emit('showErrors', false);
                        vm.field.has_value = response.data.hasValue;
                    })
                    .catch((error) => {

                    })
            },
        },
        components: {
            MaskedInput,
        }
    }
</script>
