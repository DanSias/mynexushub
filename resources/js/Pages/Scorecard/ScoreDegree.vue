<template>
    <div>
        <div class="bg-white rounded shadow hover:shadow-md duration-200 cursor-pointer h-48" @click="showFullScore = ! showFullScore" v-show="degree">
            <div class="grid grid-cols-3 px-6 py-4 text-center">
                <div class="flex flex-col items-center border-r border-gray-100">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 14l9-5-9-5-9 5 9 5z"></path><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                    <p class="mt-3 mb-1 text-2xl text-gray-800">
                        <span v-if="latestConferrals">{{ latestConferrals | commas }}</span>
                        <span v-else>&nbsp;</span>
                    </p>
                    <span class="text-gray-500 text-sm mx-2">Conferrals {{ latestConferralYear }}</span>
                </div>
                <div class="flex flex-col items-center border-r border-gray-100">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    <p class="mt-3 mb-1 text-2xl text-gray-800">
                        <span v-if="latestCAGR">{{ latestCAGR | pct1 }}</span>
                        <span v-else>&nbsp;</span>
                    </p>
                    <span class="text-gray-500 text-sm mx-2">5 Year CAGR</span>
                </div>
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="mt-3 mb-1 text-2xl text-gray-800">
                        <span v-if="searchVolume">{{ searchVolume | commas }}</span>
                        <span v-else>&nbsp;</span>
                    </p>
                    <span class="text-gray-500 text-sm mx-2">Monthly Search Volume</span>
                </div>
            </div>
        </div>
        
        <!-- Full Score Tables -->
        <div v-show="showFullScore" class="mt-8 px-3 py-4 bg-white rounded shadow hover:shadow-md">
            <table class="table-auto w-full">
                <thead>
                    <tr>
                    <th class="px-4 py-2">Category</th>
                    <th class="px-4 py-2">Value</th>
                    <th class="px-4 py-2">Score</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="latestConferrals">
                        <td class="border px-4 py-2">Degree Conferrals {{ latestConferralYear }}</td>
                        <td class="border px-4 py-2">{{ latestConferrals | commas }}</td>
                        <td class="border px-4 py-2">{{ score('conferrals') }}</td>
                    </tr>
                    <tr v-if="latestCAGR">
                        <td class="border px-4 py-2">5 Year CAGR</td>
                        <td class="border px-4 py-2">{{ latestCAGR | pct1 }}</td>
                        <td class="border px-4 py-2">{{ score('cagr') }}</td>
                    </tr>
                    <tr v-if="accreditation">
                        <td class="border px-4 py-2">Programmataic Accreditation</td>
                        <td class="border px-4 py-2">{{ accreditation | commas }}</td>
                        <td class="border px-4 py-2">{{ score('accreditation') }}</td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    name: 'DegreeScore',

    props: {
        level: {
            type: String,
            default: ''
        },
        degree: {
            type: String,
            default: ''
        },
        school: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            latestConferralYear: 2019,
            showFullScore: false,
            degreeDetails: {},
            degreeAccreditation: {},
        }
    },

    computed: {
        degreeLevel() {
            return this.level.toLowerCase()
        },
        latestConferrals() {
            if (this.degreeDetails && this.degreeDetails[this.degreeLevel]) {
                return this.degreeDetails[this.degreeLevel]['conferrals_' + this.latestConferralYear]
            }
            return 0
        },
        latestCAGR() {
            if (this.degreeDetails && this.degreeDetails[this.degreeLevel]) {
                return this.degreeDetails[this.degreeLevel]['cagr_5yr']
            }
            return 0
        },
        searchVolume() {
            if (this.degreeDetails && this.degreeDetails.search_volume && this.degreeDetails.search_volume[this.degreeLevel + '_searches']) {
                return this.degreeDetails.search_volume[this.degreeLevel + '_searches']
            }
        },
        accreditation() {
            let accreditation = ''
            if (! _.isEmpty(this.degreeAccreditation)) {
                accreditation = _.find(this.degreeAccreditation, ['school_name', this.school])
                return accreditation.accreditation
            }
            return ''
        }
    },

    methods: {
        getDegreeData() {
            axios
                .get('/scorecard/code/' + this.degree)
                .then(({data}) => this.degreeDetails = data)
        },
        getDegreeAccreditation() {
            axios
                .get('/scorecard/data/accreditation/' + this.degree)
                .then(({data}) => this.degreeAccreditation = data)
        },

        score(category) {
            switch (category) {
                case 'accreditation':
                    if (this.accreditation) {
                        return 1
                    }
                    return 0
                    break
            
                default:
                    break
            }
            return 0
        }
    },

    watch: {
        degree() {
            this.getDegreeData()
        },
        school() {
            this.getDegreeAccreditation()
        }
    },
}
</script>

<style scoped>
tr:nth-child(even) {
    background-color: #f9fafb;
}
</style>