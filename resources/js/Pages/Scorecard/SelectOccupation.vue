<template>
    <div>
        <Multiselect 
            v-model="value" 
            :options="options"
            placeholder="Occupation"
        />
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect'

export default {
    name: 'ScorecardOccupationSelect',

    components: {
        Multiselect
    },

    props: {
        degreeCode: {
            type: String,
            default: ''
        },
        currentOccupation: {
            type: String,
            default: ''
        },
        occupationType: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            value: '',
            degreeOccupations: [],
            allOccupations: []
        }
    },

    computed: {
        options() {
            if (this.occupationType == 'proxy') {
                return this.degreeOccupations
            }
            return this.allOccupations
        }
    },

    methods: {
        getDegreeOccupations() {
            axios
                .get('/scorecard/data/occupations/proxy/' + encodeURI(this.degreeCode))
                .then(({data}) => this.degreeOccupations = data)
        },
        getAllOccupations() {
            axios
                .get('/scorecard/data/occupations')
                .then(({data}) => this.allOccupations = data)
        },
    },

    watch: {
        value() {
            this.$emit('setOccupation', this.value)
        },
        degreeCode() {
            this.getDegreeOccupations()
        },
        occupationType() {
            if (this.occupationType == 'jobs') {
                this.getAllOccupations()
            } else {
                this.getDegreeOccupations()
            }
        }
    },

    mounted() {
        if (this.degreeCode && this.occupationType == 'proxy') {
            this.getDegreeOccupations()
        } else if (this.occupationType == 'jobs') {
            this.getAllOccupations()
        }
        if (this.currentOccupation) {
            this.value = this.currentOccupation
        }
    }
}
</script>