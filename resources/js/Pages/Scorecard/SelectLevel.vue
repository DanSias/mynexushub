<template>
    <div>
        <Multiselect 
            v-model="value" 
            :options="options"
            placeholder="Degree Level"
        />
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect'

export default {
    name: 'ScorecardLevelSelect',

    props: {
        currentLevel: {
            type: String,
            default: ''
        }
    },

    components: {
        Multiselect
    },

    data() {
        return {
            value: '',
            options: [
                'Bachelor',
                'Master',
                'Doctorate'
            ]
        }
    },

    watch: {
        value() {
            this.$emit('setLevel', this.value)
        }
    },

    mounted() {
        if (this.currentLevel) {
            this.value = this.currentLevel
        }
    }

}
</script>