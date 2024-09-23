<template>
    <div class="switch_button" style="margin-right: 16px" :class="{disabled: loading}">
        <input type="checkbox" class="ways_switch_input"

               @change="Switch"
               v-model="integration.active"
               id="switch_input">
        <label for="switch_input">
            <i></i>
        </label>
    </div>
</template>

<script>
    export default {
        name: "SwitchButton",
        props: ['integration', 'integrationError'],
        data() {
            return {
                loading: false
            }
        },
        methods: {

            Switch() {
                if (this.loading) {
                    return false;
                }


                let url = !!this.$props.integration.active ? `/api/integrations/${this.$props.integration.code}/activate` : `/api/integrations/${this.$props.integration.code}/deactivate`;
                this.$eventBus.$emit('loadingStart');
                this.loading = true;
                axios.post(url)
                    .then((res) => {
                        this.$emit('update:integrationError', res.data.error);
                        this.$eventBus.$emit('reloadNode');
                        this.$eventBus.$emit('loadingStop');
                        this.loading = false
                    })
                    .catch((err) => {
                        //console.log(err);
                        this.$eventBus.$emit('loadingStop');
                        this.loading = false
                    })
            }
        }

    }
</script>

<style scoped>
    .disabled {
        opacity: .8;
        pointer-events: none;
    }
</style>
