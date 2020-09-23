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
    name: 'TypeSelect',
    
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
            default: 'Type'
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
                .get('/data/programs/list/type')
                .then(({data}) => this.options = data)
        }
    },

    mounted() {
        this.getOptions()
    }
}
</script>
