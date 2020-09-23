<template>
    <multiselect class="search-box" 
        :value="value"
        :options="options" 
        :custom-label="groupLabel"
        @input="update"
        :multiple="false"
        :close-on-select="true"
        :placeholder="placeholder"
        selectLabel=">" 
        deselectLabel="x">
    </multiselect>
</template>


<script>
import Multiselect from 'vue-multiselect'

export default {
    name: 'GroupSelect',
    
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
            default: 'Group'
        }
    },
    
    data() {
        return {
            options: [
                'location',
                'bu',
                'code',
                'channel',
                'initiative',
                'partner',
                'vertical',
                'type',
                'level'
            ],
        }
    },

    methods: {
        update(value) {
            this.$emit('input', value)
        },
        groupLabel(text) {
            switch (text) {
                case 'location':
                case 'partner':
                case 'vertical':
                case 'channel':
                case 'initiative':
                    return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase() 
                    break
                case 'code':
                    return 'Program'
                    break
                case 'bu':
                    return 'Business Unit'
                    break
                case 'type':
                    return 'Degree Type'
                    break
                case 'level':
                    return 'Degree Level'
                    break
            
                default:
                    break;
            }
        }
    },

    mounted() {
    }
}
</script>
