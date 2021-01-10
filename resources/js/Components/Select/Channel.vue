<template>
    <multiselect class="search-box" 
        :value="value"
        :options="options" 
        @input="update"
        :multiple="multiple"
        :close-on-select="true"
        :placeholder="placeholder"
        selectLabel=">" 
        :open-direction="open"
        deselectLabel="x">
    </multiselect>
</template>


<script>
import Multiselect from 'vue-multiselect'

export default {
    name: 'ChannelSelectf',
    
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
            default: 'Channels'
        },
        open: {
            type: String,
            default: 'bottom'
        }
    },
    
    data() {
        return {
            options: [
                'Paid Search',
                'Paid Social',
                'SEO',
                'SEO2',
                'Portals',
                'Email',
                'Internet Advertising',
                'Referral',
                'Cultivation',
                'Corporate Partnerships',
                'Partnerships',
                'Television',
                'Chat',
                'Inbound Call',
                'Application',
                'Print',
                'Social Media',
                'Context',
                'Mail',
                'Radio',
                'Creative',
                'Outreach',
                'Outdoor',
                'MARP',
                'Other',
                'All Others'
            ],
        }
    },

    methods: {
        update(value) {
            this.$emit('input', value)
        },
    },
}
</script>
