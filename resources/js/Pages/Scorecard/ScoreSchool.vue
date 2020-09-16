<template>
    <div>
        <div class="bg-white rounded shadow hover:shadow-md cursor-pointer" @click="showFullScore = ! showFullScore">
            <div class="grid grid-cols-3 px-6 py-4 text-center">
                <div class="flex flex-col items-center border-r border-gray-100">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                    <p v-if="usnRank" class="text-lg">
                        {{ usnRank.rank }}
                    </p>
                    <span class="text-gray-500 text-sm mx-2">US News Rank <span v-if="usnRank">{{ usnRank.category }}</span></span>
                </div>
                <div class="flex flex-col items-center border-r border-gray-100">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p v-if="searchVolume" class="text-lg">
                        {{ searchVolume | commas }}
                    </p>
                    <span class="text-gray-500 text-sm mx-2">Branded Search Volume</span>
                </div>
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                    <p v-if="enrollment" class="text-lg">
                        {{ enrollment | commas }}
                    </p>
                    <span class="text-gray-500 text-sm mx-2">Enrollments {{ latestEnrollmentYear }}</span>
                </div>
            </div>
        </div>
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
                    <tr v-if="accreditation">
                        <td class="border px-4 py-2">Accrediting Body</td>
                        <td class="border px-4 py-2">{{ accreditation.abbreviation }}</td>
                        <td class="border px-4 py-2">{{ score('accreditation') }}</td>
                    </tr>
                    <tr v-if="usnRank">
                        <td class="border px-4 py-2">USN Rank</td>
                        <td class="border px-4 py-2">{{ usnRank.rank }} ({{ usnRank.category}})</td>
                        <td class="border px-4 py-2">{{ score('usnRank') }}</td>
                    </tr>
                    <tr v-if="team">
                        <td class="border px-4 py-2">NCAA Division</td>
                        <td class="border px-4 py-2">{{ team.division}}</td>
                        <td class="border px-4 py-2">{{ score('division') }}</td>
                    </tr>
                    <tr v-if="team">
                        <td class="border px-4 py-2">Power 5 Conf</td>
                        <td class="border px-4 py-2">{{ team.power5 }}</td>
                        <td class="border px-4 py-2">{{ score('power5') }}</td>
                    </tr>
                    <tr v-if="admission">
                        <td class="border px-4 py-2">Admission Competitiveness</td>
                        <td class="border px-4 py-2">{{ admission.competitiveness }}</td>
                        <td class="border px-4 py-2">{{ score('competitiveness') }}</td>
                    </tr>
                    <tr v-if="conferrals">
                        <td class="border px-4 py-2">Total Conferrals {{ latestEnrollmentYear }}</td>
                        <td class="border px-4 py-2">{{ conferrals.conferrals | commas }}</td>
                        <td class="border px-4 py-2">{{ score('conferrals') }}</td>
                    </tr>
                    <tr v-if="enrollment">
                        <td class="border px-4 py-2">Enrollments {{ latestEnrollmentYear }}</td>
                        <td class="border px-4 py-2">{{ enrollment | commas }}</td>
                        <td class="border px-4 py-2">{{ score('enrollment') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SchoolScore',

    props: {
        school: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            latestEnrollmentYear: 2018,
            showFullScore: false,
            schoolDetails: {}
        }
    },

    computed: {
        usnRank() {
            if (this.schoolDetails.rank) {
                return this.schoolDetails.rank
            }
        },
        searchVolume() {
            if (this.schoolDetails.search_volume) {
                return this.schoolDetails.search_volume.sv_total
            }
        },
        enrollment() {
            if (this.schoolDetails.enrollment) {
                return this.schoolDetails.enrollment.enrollment
            }
        },
        accreditation() {
            if (this.schoolDetails.accreditation) {
                return this.schoolDetails.accreditation
            }
        },
        admission() {
            if (this.schoolDetails.admission) {
                return this.schoolDetails.admission
            }
        },
        team() {
            if (this.schoolDetails.team) {
                return this.schoolDetails.team
            }
        },
        conferrals() {
            if (this.schoolDetails.total_conferrals) {
                return this.schoolDetails.total_conferrals
            }
        }
    },

    methods: {
        getSchoolData() {
            axios
                .get('/scorecard/school/' + this.school)
                .then(({data}) => this.schoolDetails = data)
        },
        score(category) {
            return 111;
        }
    },

    watch: {
        school() {
            this.getSchoolData()
        }
    },
}
</script>

<style scoped>
tr:nth-child(even) {
    background-color: #f9fafb;
}
</style>