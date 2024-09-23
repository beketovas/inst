<template>
    <div class="select tags_select" >
        <div class="dropdown field-available-related select_list" >
            <div v-show="field.available_related_fields">
                <div class="field-box select_list__item" v-for="relatedField in field.available_related_fields"
                     @click="insertField($event, relatedField)">
                    {{ relatedField.title }}
                    <span class="dots">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </span>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {CodemirrorField} from '@/js/modules/codemirror/field'

export default {
    inject: ['IntegrationComponent'],
    props: ['field'],
    mounted() {
        setTimeout(()=>{
            document.querySelectorAll('.select_list').forEach(item=>{
                Scrollbar.init(item, {
                    alwaysShowTracks: true
                })
            })
        },500)
    },
    data() {
        return {}
    },
    watch: {},
    methods: {
        insertField(event, field) {
            let codemirrorEl = event.target.closest('.field-related-container').querySelector('.CodeMirror');
            let editor = codemirrorEl.CodeMirror;

            let cmw = new CodemirrorField(editor);

            cmw.insertMark(field.id, field.title, field.identifier);

            this.$parent.codemirrorChanged(editor, event, this.field);
        },

    },
    components: {},
}
</script>
