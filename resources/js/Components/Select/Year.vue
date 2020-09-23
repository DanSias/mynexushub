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
    name: 'YearSelect',
    
    components: { Multiselect },

    props: {
        value: {
            type: [Array, String, Number],
            default: () => []
        },
        multiple: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default: 'Year'
        }
    },
    
    data() {
        return {
            options: [
                2020,
                2019,
                2018,
                2017,
                2016
            ]
        }
    },

    methods: {
        update(value) {
            this.$emit('input', value)
        },
    }
}
</script>
