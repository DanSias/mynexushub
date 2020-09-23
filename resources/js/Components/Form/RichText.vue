<template>
    <div>
        <vue-editor 
            class="rich-text-input mb-2" 
            v-model="content" 
            @change="update"
            :editorToolbar="customToolbar" 
        />
    </div>
</template>

<script>
import { VueEditor } from 'vue2-editor'

export default {

    name: 'FormRichText',

    props: {
        value: {
            type: [Array, Object, String],
            default: () => []
        },
    },

    data() {
        return {
            content: '',
            customToolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'color': [] }, { 'background': [] }],   
                ['blockquote'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ],
        }
    },

    components: {
        VueEditor,
    },

    methods: {
        update(value) {
            this.$emit('input', value)
        },
    },

    watch: {
        content() {
            this.update(this.content)
        },
        value() {
            this.content = this.value
        }
    },

    mounted() {
        if (this.value) {
            this.content = this.value
        }
    }
}
</script>
