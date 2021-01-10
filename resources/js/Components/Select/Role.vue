<template>
    <multiselect class="border-gray-600" 
        :value="value"
        @input="update"
        :options="titleList" 
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
        team: {
            type: String,
            default: ''
        }
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

        titleList() {
            let userTeam = this.team
            let team = this.teams;
            let list = team.filter(t => t.name == userTeam);
            if (list[0]) {
                let levels = list[0].levels;
                console.log(list);
                return levels;
            } else {
                return [];
            }
        },
    },

    methods: {
        update(value) {
            this.$emit('input', value)
        },
    }
}
</script>
