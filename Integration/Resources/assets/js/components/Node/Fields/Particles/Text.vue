<template>
    <div class="text-field-container select">
        <div class="input-textarea main_input select_selected plus added_fake__input">
            <div class="added_fake__input_wrap">
                <codemirror ref="myCm"
                            :value="stringFieldValue"
                            :options="cmOptions"
                            v-model="stringFieldValue"
                            v-on:blur="codemirrorChanged"
                            @ready="onCmReady"
                            @input="onCmCodeChange"
                />
            </div>
        </div>
        <div class="plus added_fake__input_icon" @click="showAvailableFields">
            <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"><path
                fill="#61616A"
                d="M17 7.625H11.375V2C11.375 1.66146 11.2448 1.375 10.9844 1.14062C10.75 0.880208 10.4635 0.75 10.125 0.75H8.875C8.53646 0.75 8.23698 0.880208 7.97656 1.14062C7.74219 1.375 7.625 1.66146 7.625 2V7.625H2C1.66146 7.625 1.36198 7.75521 1.10156 8.01562C0.867188 8.25 0.75 8.53646 0.75 8.875V10.125C0.75 10.4635 0.867188 10.763 1.10156 11.0234C1.36198 11.2578 1.66146 11.375 2 11.375H7.625V17C7.625 17.3385 7.74219 17.625 7.97656 17.8594C8.23698 18.1198 8.53646 18.25 8.875 18.25H10.125C10.4635 18.25 10.75 18.1198 10.9844 17.8594C11.2448 17.625 11.375 17.3385 11.375 17V11.375H17C17.3385 11.375 17.625 11.2578 17.8594 11.0234C18.1198 10.763 18.25 10.4635 18.25 10.125V8.875C18.25 8.53646 18.1198 8.25 17.8594 8.01562C17.625 7.75521 17.3385 7.625 17 7.625Z"></path>
                /&gt;
            </svg>
        </div>
    </div>
</template>

<script>
    import { codemirror } from 'vue-codemirror'
    import 'codemirror/lib/codemirror.css'
    import {CodemirrorField} from '@/js/modules/codemirror/field'

    export default {
        props: ['field'],
        data() {
            return {
                fieldValue: this.field.value,
                cmf: null,
                cmOptions: {
                    lineWrapping: true
                }
            }
        },
        watch: {
            field() {
                this.stringFieldValue = this.field.value
            }
        },
        computed: {
            codemirror() {
                return this.$refs.myCm.codemirror
            },
            stringFieldValue: {
                get: function () {
                    if(this.fieldValue)
                        return this.fieldValue;
                    else
                        return '';
                },
                set: function (newValue) {
                    if(newValue === null)
                        newValue = '';

                    this.fieldValue = newValue;
                }
            }
        },
        methods: {
            codemirrorChanged(cm, event) {
                this.$parent.codemirrorChanged(cm, event, this.field);
            },
            showAvailableFields(event) {
                this.$parent.showAvailableFields(event, this.field);
            },
            onCmReady(cm) {
                this.cmf = new CodemirrorField(cm);
                this.cmf.parse(this.field.marks);
            },
            onCmCodeChange(newCode) {
                this.cmf.parse(this.field.marks);
            }
        },
        components: {
            codemirror
        },
        mounted() {

        }
    }
</script>
