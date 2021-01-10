<template>
    <multiselect class="search-box" 
        :value="value"
        :options="options" 
        @input="update"
        :multiple="multiple"
        :close-on-select="true"
        :placeholder="placeholder" 
        selectLabel=">" 
        :maxHeight="max"
        deselectLabel="x">
    </multiselect>
</template>


<script>
import Multiselect from 'vue-multiselect'

export default {
    name: 'BuSelect',
    
    components: { Multiselect },

    props: {
        value: {
            type: [Array, Number, String],
            default: () => []
        },
        multiple: {
            type: Boolean,
            default: true
        },
        placeholder: {
            type: String,
            default: 'Business Unit'
        },
        max: {
            type: Number,
            default: 300
        },
        filter: {
            type: [Object],
            default: () => {}
        },
    },
    
    data() {
        return {
            options: []
        }
    },

    computed: {
        location() {
            return (this.filter && this.filter.location) ? this.filter.location : ''
        }
    },

    methods: {
        update(value) {
            this.$emit('input', value)
        },
        getOptions() {
            axios
                .get('/data/programs/list/bu', {
                    params: {
                        filter: this.filter
                    }
                })
                .then(({data}) => this.options = data)
        }
    },

    watch: {
        location() {
            this.getOptions()
        }
    },

    mounted() {
        this.getOptions()
    }
}
</script>
