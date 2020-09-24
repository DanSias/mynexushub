<template>
    <!-- Forecast Home -->
    <div class="bg-white mt-8 rounded shadow w-full">
        <div class="px-6 py-4 flex justify-between border-b border-gray-300">
            <span v-if="tableType == 'progress'">
                Current Forecast Progress
                <!-- <font-awesome-icon icon="question-circle" class="uk-margin-left uk-text-muted" />
                <div class="uk-width-large" :delay-hide="100">
                    <div>
                        <p>Forecast progress of all expected channels for each program, based on budget and existing forecast.</p>
                        <p>Click on a program to see all channel forecasts for that program, click on the channel to see initiative level inputs for that channel.</p>
                        <p><span class="uk-text-primary">Blue Channels</span> have been submitted by the channel owner and are awaiting approval.</p>
                        <p><span class="uk-text-success">Green Channels</span> have been approved by the ADMA.</p>
                    </div>
                </div> -->
            </span>
            <span v-else class="capitalize">
                {{ month }} Forecast vs Previous {{ tableType }}
            </span>

            <div>
                <div class="flex">
                    <div v-for="(type, i) in ['progress', 'variance', 'percent', 'values']" :key="i">
                        <span 
                            class="capitalize ml-4 cursor-pointer" 
                            :class="(tableType == type) ? 'bg-blue-500 text-white rounded-full py-1 px-4' : 'text-gray-800 hover:text-blue-500' "
                            @click="tableType = type"
                        >
                            {{ type }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <variance-table 
            v-if="ready && tableType != 'progress'"
            :tableType="tableType"
            :programs="filter.programs"
            :expected="expected"
            :progress="forecasts"
            :year="year"
            :month="month"
        />
        <div class="p-6">
            <table v-show="tableType == 'progress'" class="w-full table-auto">
                <thead>
                    <tr>
                        <th class="px-4 py-2 w-56">Program</th>
                        <th class="px-4 py-2">Channels</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(program, i) in filter.programs" :key="i" class="hover:bg-yellow-50" :class="(i%2 == 0) ? 'bg-gray-50' : ''">
                        <td class="program-column border px-4 py-2">
                            <!-- Program -->
                            <inertia-link :href="`/forecast/${program}`">
                                <span class="text-gray-800 font-semibold" @click="goHere(program, '')" :aria-label="`Submitted: ${submittedPercent[program]} | Approved: ${approvedPercent[program]}`" data-balloon-pos="right">
                                    {{ program }}
                                </span>
                            </inertia-link>
                        </td>
                        <td class="border px-4 py-2">
                            <span v-if="[program]">
                                <span v-for="(channel, j) in programChannels(program)" :key="j" @click="goHere(program, channel)">
                                    <inertia-link :href="`/forecast/${program}/${channel}`">
                                        <span :class="(hasForecast(program, channel)) ? hasForecast(program, channel) : 'text-gray-500 hover:text-gray-700'">
                                            {{ channel }}
                                        </span>
                                    </inertia-link>
                                    <!-- Separate with pipe -->
                                    <span v-if="j != Object.keys(programChannels(program)).length - 1" class="text-gray-300 ml-2 mr-2"> | </span>
                                </span>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import VarianceTable from './VarianceTable'

export default {
    name: 'ForecastIndex',
   
    components: {
        VarianceTable,
    },

    props: {
        filter: {
            type: Object,
            default: () => {}
        },
        year: {
            type: Number,
            default: 0
        },
        month: {
            type: String,
            default: ''
        },
        status: {
            type: String,
            default: ''
        },
        program: {
            type: String,
            default: ''
        },
        channel: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            settings: {},
            ready: false,
            showColumns: false,
            currentForecast: {},
            columns: {},
            expected: {},
            forecasts: {},
            tableType: 'progress'
        }
    },

    computed: {
        expectedWithForecasts() {
            let expected = this.expected;
            let submitted = this.forecasts.submitted;
            let approved = this.forecasts.approved;

            _.forEach(_.keys(submitted), key => {
                if (! expected[key]) {
                    expected[key] = submitted[key];
                } else {
                    _.forEach(submitted[key], channel => {
                        if (! expected[key].includes(channel)) {
                            expected[key].push(channel);
                        }
                    })
                }
            })
            _.forEach(_.keys(approved), key => {
                if (! expected[key]) {
                    expected[key] = approved[key];
                } else {
                    _.forEach(submitted[key], channel => {
                        if (! expected[key].includes(channel)) {
                            expected[key].push(channel);
                        }
                    })
                }
            });
            return expected;
        },
        submittedPercent() {
            let progress = {};
            let expected = 0;
            let submitted = 0;
            if (! _.isEmpty(this.forecasts)) {
                _.forEach(this.filter.programs, p => {
                    if (! _.isEmpty(this.forecasts.submitted[p]) && ! _.isEmpty(this.expected[p])) {
                        progress[p] = (this.expected[p].length > 0) ? this.forecasts.submitted[p].length / this.expected[p].length : 0;
                        expected += this.expected[p].length;
                        submitted += this.forecasts.submitted[p].length;
                    } else if (! _.isEmpty(this.expected[p])) {
                        progress[p] = 0;
                        expected += this.expected[p].length;
                    } else {
                        progress[p] = 0;
                    }
                });
            }
            progress.ALL = (expected > 0) ? submitted / expected : 0;
            progress.expected = expected;
            progress.submitted = submitted;
            return progress;
        },
        approvedPercent() {
            let progress = {};
            let expected = 0;
            let approved = 0;
            if (! _.isEmpty(this.forecasts)) {
                _.forEach(this.filter.programs, p => {
                    if (! _.isEmpty(this.forecasts.approved[p]) && ! _.isEmpty(this.expected[p])) {
                        progress[p] = (this.expected[p].length > 0) ? this.forecasts.approved[p].length / this.expected[p].length : 0;
                        expected += this.expected[p].length;
                        approved += this.forecasts.approved[p].length;
                    } else if (! _.isEmpty(this.expected[p])) {
                        progress[p] = 0;
                        expected += this.expected[p].length;
                    } else {
                        progress[p] = 0;
                    }
                });
            }
            progress.ALL = (expected > 0) ? approved / expected : 0;
            progress.expected = expected;
            progress.approved = approved;
            return progress;
        },
    },
 
    methods: {
        getSettings() {
            axios.get('/api/forecast/settings').then(({data}) => this.settings = data);
        },
        programChannels(program) {
            if (_.isEmpty(this.channel)) {
                return this.expectedWithForecasts[program];
            } else {
                return this.channel;
            }
        },
        hasForecast(program, channel) {
            if (this.forecasts && this.forecasts.approved && this.forecasts.approved[program]) {
                if (this.forecasts.approved[program].includes(channel)) {
                    return 'text-green-500';
                }           
            }
            if (this.forecasts && this.forecasts.submitted && this.forecasts.submitted[program]) {
                if (this.forecasts.submitted[program].includes(channel)) {
                    return 'text-blue-500';
                }
            }
            return '';
        },
        goHere(program, channel) {
            const router = this.$router;
            if (channel == '') {
                router.push('/forecast/' + program);
            } else {
             router.push('/forecast/' + program + '/' + channel);
            }
        },
        getExpected() {
            axios
                .get('/data/forecast/expected', {
                    params: {
                        filter: this.filter
                    }
                })
                .then(({data}) => {
                    this.expected = data;
                    this.ready = true;
                })
        },
        getCurrent() {
            axios
                .get('/data/forecast/has-forecast', {
                    params: {
                        filter: this.filter
                    }
                })
                .then(({data}) => {
                    this.forecasts = data;
                })
        }
    },

    mounted() {
        // this.setCurrentFullYear();
        // this.resetUserFilter();
        this.getExpected();
        this.getCurrent();
    },

    watch: {

    },
}
</script>

<style scoped>
.gear-icon {
    margin-top: 4px;
}
.uk-card-body {
    padding-top: 20px;
}
.program-column {
    width: 160px;
}
</style>