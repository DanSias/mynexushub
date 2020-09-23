<template>
    <multiselect
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
    name: 'StrategySelect',
    
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
            default: 'Strategy'
        }
    },
    
    data() {
        return {
            options: [
                'Invest',
                'Optimize',
                'Divest',
                'Cut',
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
