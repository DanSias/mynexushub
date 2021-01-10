<template>
    <multiselect class="border-gray-600" 
        :value="value"
        @input="update"
        :options="teamList" 
        placeholder="" 
        selectLabel=">" 
        deselectLabel="x">
    </multiselect>
</template>


<script>
import Multiselect from 'vue-multiselect'
import TeamsMixin from '../../Mixins/Teams'

export default {
    name: 'TeamSelect',

    mixins: [TeamsMixin],

    props: {
        value: {
            type: [Array, Object, String],
            default: () => []
        },
    },

    components: {
        Multiselect
    },

    data() {
        return {
        }
    },

    computed: {
        teamList() {
            let list = [];
            for (const t in this.teams) {
                list.push(this.teams[t].name);
            }
            return list.sort();
        },
    },

    methods: {
        update(value) {
            this.$emit('input', value)
        },
    }
}
</script>
