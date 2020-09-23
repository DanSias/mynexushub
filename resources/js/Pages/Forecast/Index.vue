<template>
    <!-- Forecast Home -->
    <div>
        <div class="container mx-auto">
            <div class="uk-margin-top uk-margin-bottom">
                <div slot="header">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="uk-width-expand">
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
                            <span v-else>
                                {{ month }} Forecast vs Previous {{ tableType | ucFirst }}
                            </span>
                        </div>
                        <div>
                            <div v-if="ready" class="uk-margin uk-grid-small uk-child-width-auto uk-grid uk-text-mute">
                                <label><input class="uk-radio" type="radio" value="progress" v-model="tableType"> Progress</label>
                                <label><input class="uk-radio" type="radio" value="variance" v-model="tableType"> Variance</label>
                                <label><input class="uk-radio" type="radio" value="percent" v-model="tableType"> Percent</label>
                                <label><input class="uk-radio" type="radio" value="values" v-model="tableType"> Values</label>
                            </div>
                        </div>
                    </div>
                </div>

                <variance-table 
                    v-if="ready && tableType != 'progress'"
                    :tableType="tableType"
                    :programs="filteredProgramsList"
                    :expected="expected"
                    :progress="forecasts"
                />

                <table v-show="tableType == 'progress'" class="uk-table uk-table-striped uk-table-hover uk-table-small uk-table-middle nexus-table uk-margin-remove-top">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Channels</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(program, i) in filteredProgramsList" :key="i">
                            <td class="program-column">
                                <span @click="goHere(program, '')" class="clickable">{{ program }}</span>
                                <div position="right-center">
                                    <div >
                                        Submitted: <span class="uk-float-right">{{ submittedPercent[program]  | pct0 }}</span><br>
                                        Approved: <span class="uk-float-right">{{ approvedPercent[program]  | pct0 }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span v-if="[program]">
                                    <span v-for="(channel, j) in programChannels(program)" :key="j" @click="goHere(program, channel)">
                                        <span class="clickable" :class="hasForecast(program, channel)">
                                            {{ channel }}
                                        </span>
                                        <!-- Separate with pipe -->
                                        <span v-if="j != Object.keys(programChannels(program)).length - 1" class="uk-margin-left uk-margin-right uk-text-muted"> | </span>
                                    </span>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import VarianceTable from './VarianceTable';

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
        filteredProgramsList() {
            return ['ABC-XZY', 'DNA-BOB']
        },
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
                _.forEach(this.filteredProgramsList, p => {
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
                _.forEach(this.filteredProgramsList, p => {
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
                    return 'uk-text-success';
                }           
            }
            if (this.forecasts && this.forecasts.submitted && this.forecasts.submitted[program]) {
                if (this.forecasts.submitted[program].includes(channel)) {
                    return 'uk-text-primary';
                }
            }
            return 'uk-text-muted';
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