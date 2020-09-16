<template>
    <div>
        <Multiselect 
            v-model="value" 
            :options="options"
            placeholder="Institution Name"
        />
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect'

export default {
    name: 'ScorecardSchoolSelect',

    components: {
        Multiselect
    },

    props: {
        currentSchool: {
            type: String,
            default: ''
        },
        schoolType: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            value: '',
            allSchools: [],
            partners: [
                'Adelphi University',
                'Arizona State University-Tempe',
                'Bradley University',
                'Brandeis University',
                'Case Western Reserve University',
                'Duquesne University',
                'Eastern Kentucky University',
                'George Washington University',
                'Hofstra University',
                'Kent State University at Kent',
                'Maryville University of Saint Louis',
                'Misericordia University',
                'New England College',
                'New Jersey Institute of Technology',
                'Northeastern University',
                'Norwich University',
                'Ohio University-Main Campus',
                'Pepperdine University',
                'Rider University',
                'Regis College',
                'Rutgers University-New Brunswick',
                'University of Alabama at Birmingham',
                'University of Arizona',
                'University of California-Riverside',
                'University of Cincinnati-Main Campus',
                'University of Illinois at Chicago',
                'University of Maryland-College Park',
                'University of Nevada-Reno',
                'University of San Francisco',
                'University of Southern California',
                'Villanova University',
                'Wake Forest University',
                'Washington State University',
            ]
        }
    },

    computed: {
        options() {
            if (this.schoolType == 'partners') {
                return this.partners
            }
            return this.allSchools
        }
    },

    methods: {
        getAllSchools() {
            axios
                .get('/scorecard/data/schools')
                .then(({data}) => this.allSchools = data)
        },
    },

    watch: {
        value() {
            this.$emit('setSchool', this.value)
        }
    },

    mounted() {
        this.getAllSchools()
        if (this.currentSchool) {
            this.value = this.currentSchool
        }
    }
}
</script>