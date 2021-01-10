<template>
    <!-- Forecast Home -->
    <div class="bg-white mt-8 rounded shadow w-full">
        <div class="px-6 py-4 flex justify-between border-b border-gray-300">
            <span v-if="tableType == 'progress'" class="flex">
                Current Forecast Progress
                <span ref="trigger" v-on:mouseenter="togglePopover()" v-on:mouseleave="togglePopover()"  class="ml-4 text-gray-300 hover:text-gray-600 duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
                <div 
                    ref="pop" 
                    :class="(showPopover) ? 'block' : 'hidden'" 
                    class="border border-gray-200 mt-1 block z-50 text-sm shadow-xl text-left no-underline break-words transition duration-300 ease-in-out"
                >
                    <div>
                        <div class="bg-gray-50 text-gray-800 font-semibold p-3 mb-0 border-b border-solid border-gray-200 capitalize">
                            Progress Table
                        </div>
                        <div class="bg-white p-6 max-w-lg">
                            <p>Forecast progress of all expected channels for each program, based on budget and existing forecast.</p>
                            <p class="mt-2">Click on a program to see all channel forecasts for that program, click on the channel to see initiative level inputs for that channel.</p>
                            <p class="mt-2"><span class="text-blue-500 font-bold">Blue Channels</span> have been submitted by the channel owner and are awaiting approval.</p>
                            <p class="mt-2"><span class="text-green-500 font-bold">Green Channels</span> have been approved by the ADMA.</p>
                        </div>
                    </div>
                </div>
            </span>
            <span v-else class="capitalize">
                {{ month }} Forecast vs Previous {{ tableType }}
            </span>

            <div>
                <div class="flex">
                    <div v-for="(type, i) in ['progress', 'variance', 'percent', 'values']" :key="i">
                        <span 
                            class="capitalize ml-4 cursor-pointer py-1 px-4 rounded-full " 
                            :class="(tableType == type) ? 'bg-gray-800 text-white ' : 'text-gray-800 hover:bg-gray-100' "
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
            :programs="programList"
            :expected="expected"
            :progress="forecasts"
            :year="year"
            :month="month"
        />
        <div class="p-6">
            <table v-show="tableType == 'progress'" class="w-full table-auto">
                <thead>
                    <tr class="text-gray-500">
                        <th class="px-4 py-2 w-56">Program</th>
                        <th class="px-4 py-2">Channels</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(program, i) in programList" :key="i" class="hover:bg-yellow-50" :class="(i%2 == 0) ? 'bg-gray-50' : ''">
                        <td class="program-column border px-4 py-2">
                            <!-- Program -->
                            <inertia-link :href="`/forecast/${program}`">
                                <span class="text-gray-800 font-semibold" :aria-label="`Submitted: ${submittedPercent[program]} \nApproved: ${approvedPercent[program]}`" data-balloon-pos="right" data-balloon-break>
                                    {{ program }}
                                </span>
                            </inertia-link>
                        </td>
                        <td class="border px-4 py-2">
                            <span v-if="[program]">
                                <span v-for="(channel, j) in programChannels(program)" :key="j">
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
import Popper from "popper.js";
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
            tableType: 'progress',
            programList: [],
            showPopover: false
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
                _.forEach(this.programList, p => {
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
                _.forEach(this.programList, p => {
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
        filterString() {
            let string = ''
            _.forEach(this.filter, (value, key) => {
                string += (value) ? key + value.toString() : ''
            })
            return string
        }
    },
 
    methods: {
        togglePopover() {
            if(this.showPopover){
                this.showPopover = false
            } else {
                this.showPopover = true
                new Popper(this.$refs.trigger, this.$refs.pop, {
                    placement: "bottom-start"
                })
            }
        },
        getSettings() {
            axios.get('/api/forecast/settings').then(({data}) => this.settings = data);
        },
        programChannels(program) {
            if (_.isEmpty(this.filter.channel)) {
                return this.expectedWithForecasts[program];
            } else {
                return this.filter.channel;
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
        },
        getProgramList() {
            axios
                .get('/data/programs/list/code', {
                    params: {
                        filter: this.filter
                    }
                })
                .then(({data}) => {
                    this.programList = data
                })
        }
    },

    mounted() {
        // this.setCurrentFullYear()
        // this.resetUserFilter()
        this.getExpected()
        this.getCurrent()
        this.getProgramList()
    },

    watch: {
        filterString() {
            this.getProgramList()
            this.getCurrent()
            this.getExpected()
        }
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