<template>
    <div>
        <div class="bg-white rounded shadow hover:shadow-md duration-200 cursor-pointer h-48" @click="showFullScore = ! showFullScore">
            <div class="grid grid-cols-3 px-6 py-4 text-center">
                <div class="flex flex-col items-center border-r border-gray-100">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <p class="text-lg">
                        <span v-if="latestYearJobs">{{ latestYearJobs | commas }}</span>
                        <span v-else>&nbsp;</span>
                    </p>
                    <span class="text-gray-500 text-sm mx-2">Number of Jobs {{ latestYear }}</span>
                </div>
                <div class="flex flex-col items-center border-r border-gray-100">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-lg">
                        <span v-if="latestYearOutlook">{{ latestYearOutlook | pct0 }}</span>
                        <span v-else>&nbsp;</span>
                    </p>
                    <span class="text-gray-500 text-sm mx-2">Job Outlook {{ latestYear }} - {{ latestYear + 10 }}</span>
                </div>
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <p class="text-lg">
                        <span v-if="jobPostings">{{ jobPostings | commas }}</span>
                        <span v-else>&nbsp;</span>
                    </p>
                    <span class="text-gray-500 text-sm mx-2">Burning Glass Job Postings</span>
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
                    <tr v-if="latestYearJobs">
                        <td class="border px-4 py-2">Number of Jobs {{ latestYear }}</td>
                        <td class="border px-4 py-2">{{ latestYearJobs | commas }}</td>
                        <td class="border px-4 py-2">{{ score('jobs') }}</td>
                    </tr>
                    <tr v-if="latestYearOutlook">
                        <td class="border px-4 py-2">Job Outlook {{ latestYear }} - {{ latestYear + 10 }}</td>
                        <td class="border px-4 py-2">{{ latestYearOutlook | pct0 }}</td>
                        <td class="border px-4 py-2">{{ score('outlook') }}</td>
                    </tr>
                    <tr v-if="jobPostings">
                        <td class="border px-4 py-2">Burning Glass Job Postings</td>
                        <td class="border px-4 py-2">{{ jobPostings | commas }}</td>
                        <td class="border px-4 py-2">{{ score('postings') }}</td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    name: 'OccupationScore',

    props: {
        occupation: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            latestYear: 2018,
            showFullScore: false,
            occupationDetails: {}
        }
    },

    computed: {
        growthRate() {
            if (this.occupationDetails.growth_rate) {
                return this.occupationDetails.growth_rate
            }
            return null
        },
        latestYearJobs() {
            if (this.growthRate) {
                return this.growthRate['employment_' + this.latestYear]
            }
        },
        latestYearOutlook() {
            if (this.growthRate) {
                return this.growthRate['growth_rate']
            }
        },
        jobPostings() {
            if (this.occupationDetails.postings) {
                return this.occupationDetails.postings
            }
        }
    },

    methods: {
        getOccupationData() {
            axios
                .get('/scorecard/occupation/' + this.occupation)
                .then(({data}) => this.occupationDetails = data)
        },
        score(category) {
            return 123
        }
    },

    watch: {
        occupation() {
            this.getOccupationData()
        }
    },
}
</script>

<style scoped>
tr:nth-child(even) {
    background-color: #f9fafb;
}
</style>