<template>
    <multiselect class="search-box" 
        :value="value"
        :options="options" 
        @input="update"
        :multiple="multiple"
        :close-on-select="true"
        :placeholder="placeholder"
        selectLabel=">" 
        deselectLabel="x">
    </multiselect>
</template>


<script>
import Multiselect from 'vue-multiselect'

export default {
    name: 'VerticalSelect',
    
    components: { Multiselect },

    props: {
        value: {
            type: [Array, Object, String],
            default: () => []
        },
        multiple: {
            type: Boolean,
            default: true
        },
        placeholder: {
            type: String,
            default: 'Vertical'
        },
        vertical: {
            type: String,
            default: 'Vertical'
        }
    },
    
    data() {
        return {
            options: []
        }
    },

    methods: {
        update(value) {
            this.$emit('input', value)
        },
        getOptions() {
            axios
                .get('/data/programs/list/subvertical/' + this.vertical)
                .then(({data}) => this.options = data)
        }
    },

    watch: {
        vertical() {
            this.getOptions()
        }
    },

    mounted() {
        this.getOptions()
    }
}
</script>
