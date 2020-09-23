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
    name: 'LocationSelect',
    
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
            default: 'Location'
        }
    },
    
    data() {
        return {
            options: [
                'Orlando',
                'Toronto',
                'Chicago',
                'Chandler',
                'ASU',
                'MVU'
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
