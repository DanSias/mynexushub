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
    name: 'ChannelInitiativeSelect',
    
    components: { Multiselect },

    props: {
        channel: {
            type: String,
            default: ''
        },
        value: {
            type: [Array, Number, String],
            default: () => []
        },
        multiple: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default: 'Initiatives...'
        },
        max: {
            type: Number,
            default: 300
        },
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
                .get('/data/channels/list/initiatives/' + this.channel)
                .then(({data}) => this.options = data)
        }
    },

    mounted() {
        this.getOptions()
    }
}
</script>
