<template>
    <multiselect class="search-box" 
        :value="value"
        :options="options" 
        @input="update"
        :custom-label="capitalize"
        :multiple="multiple"
        :close-on-select="closeOnSelect"
        placeholder="" 
        selectLabel=">" 
        deselectLabel="x">
    </multiselect>
</template>


<script>
import Multiselect from 'vue-multiselect'

export default {
    name: 'MonthSelect',
    
    components: { Multiselect },

    props: {
        value: {
            type: [Array, String],
            default: () => []
        },
        multiple: {
            type: Boolean,
            default: true
        }
    },
    
    data() {
        return {
            options: [
                'january',
                'february',
                'march',
                'april',
                'may',
                'june',
                'july',
                'august',
                'september',
                'october',
                'november',
                'december'
            ]
        }
    },

    computed: {
        placeholder() {
            return (this.multiple) ? 'Months' : 'Month'
        },
        closeOnSelect() {
            return (this.multiple) ? false : true
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
