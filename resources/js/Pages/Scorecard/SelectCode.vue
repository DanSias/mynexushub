<template>
    <div>
        <Multiselect 
            v-model="value" 
            :options="options"
            track-by="cip6d"
            label="term"
            :placeholder="placeholder"
        />
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect'

export default {
    name: 'ScorecardCodeSelect',

    props: {
        degreeLevel: {
            type: String,
            default: ''
        },
        degreeType: {
            type: String,
            default: ''
        },
        currentDegree: {
            type: String,
            default: ''
        },
    },

    components: {
        Multiselect
    },

    data() {
        return {
            value: null,
            keywords: [],
            conferralCodes: [],
            allCodes: [],
        }
    },

    computed: {
        placeholder() {
            return (this.degreeType == 'keywords') ? 'Degree Term' : 'Degree CIP Code'
        },
        options() {
            switch (this.degreeType) {
                case 'keywords':
                    return this.keywords
                    break
                case 'conferrals':
                    return this.conferralCodes
                    break
                case 'codes':
                    return this.allCodes
                    break
                default:
                    return []
                    break
            }
        }
    },

    methods: {
        getKeywords() {
            axios
                .get('/scorecard/data/keywords')
                .then(({data}) => this.keywords = data)
        },
        getConferralCodes() {
            axios
                .get('/scorecard/data/codes-conferrals/' + this.degreeLevel)
                .then(({data}) => this.conferralCodes = data)
        },
        getAllCodes() {
            axios
                .get('/scorecard/data/codes')
                .then(({data}) => this.allCodes = data)
        },
    },

    watch: {
        value() {
            this.$emit('setCode', this.value.cip6d)
        },
        degreeLevel() {
            if (this.degreeType == 'conferrals') {
                this.getConferralCodes()
            }
        }
    },

    mounted() {
        if (this.currentDegree) {
            this.value = {
                cip6d: this.currentDegree,
                term: this.currentDegree
            }
        }
        this.getKeywords()
        this.getConferralCodes()
        this.getAllCodes()
    }

}
</script>