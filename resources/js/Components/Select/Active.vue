<template>
    <multiselect class="search-box" 
        :value="value"
        :options="options" 
        @input="update"
        :custom-label="capitalize"
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
    name: 'ActiveSelect',

    components: { Multiselect },

    props: {
        value: {
            type: [Array, String],
            default: () => []
        },
        multiple: {
            type: Boolean,
            default: true
        },
        placeholder: {
            type: String,
            default: 'Active Status'
        }
    },

    data() {
        return {
            options: [
                'TRUE', 
                'PENDING',
                // 'ROLLUP',
                'FALSE'
            ]
        }
    },

    methods: {
        update(value) {
            this.$emit('input', value)
        },
        capitalize(string) {
            return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase() 
        }
    }
}
</script>
