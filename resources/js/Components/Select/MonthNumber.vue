<template>
    <multiselect class="search-box" 
        v-model="monthText"
        :options="months" 
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
    name: 'MonthNumberSelect',
    
    components: { Multiselect },

    props: {
        value: {
            type: [Array, String, Number],
            default: () => []
        },
        multiple: {
            type: Boolean,
            default: true
        }
    },
    
    data() {
        return {
            monthText: '',
            months: [
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
            let index = _.findIndex(this.months, m => {return m == value})
            let number = index + 1
            this.$emit('input', number)
        },
        capitalize(string) {
            return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase() 
        }
    },

    // watch: {
    //     monthText() {
    //         let index = _.findIndex(this.months, this.monthText)
    //         this.update(index + 1)
    //     },
    // },

    mounted() {
        if (this.value) {
            let index = parseInt(this.value) - 1
            this.monthText = this.months[index]
        }
    }
}
</script>
