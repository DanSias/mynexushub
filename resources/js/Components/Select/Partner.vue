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
    name: 'PartnerSelect',
    
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
            default: 'Academic Partner'
        },
        filter: {
            type: Object,
            default: () => {}
        }
    },
    
    data() {
        return {
            options: []
        }
    },

    computed: {
        location() {
            return (this.filter.location) ? this.filter.location : ''
        },
        bu() {
            return (this.filter.bu) ? this.filter.bu : ''
        }
    },

    methods: {
        update(value) {
            this.$emit('input', value)
        },
        getOptions() {
            axios
                .get('/data/programs/list/partner', {
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
        },
        bu() {
            this.getOptions()
        }
    },

    mounted() {
        this.getOptions()
    }
}
</script>
