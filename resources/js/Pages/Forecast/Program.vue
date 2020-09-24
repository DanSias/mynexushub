<template>
    <div class="bg-white mt-8 rounded shadow w-full">

        <div class="uk-margin-top uk-margin-bottom" v-show="ready">
            <div class="px-6 py-4 flex justify-between border-b border-gray-300">
                <div>
                    New Forecast Compared to the Existing Forecast
                    <!-- <font-awesome-icon icon="question-circle" class="uk-text-muted uk-margin-left" /> -->
                    <!-- <vk-drop class="uk-width-large" :delay-hide="100">
                        <div>
                            <p>Leads, Spend, And CPL Differences by channel to compare this new <span class="capitalize">{{ month }}</span> forecast to the forecast that was submitted last month.</p>
                            <p>The left side of the table shows the variance for the upcoming month, and the right half compares the entire year. Hover over any value</p>
                            <p>Any row with <span class="uk-text-muted">n/a</span> means that channel has not yet submitted a forecast.</p>
                            <p>Click on a channel to view initiative level inputs.</p>
                        </div>
                    </vk-drop> -->
                </div>
                <div class="flex">
                    <div v-for="(type, i) in ['variance', 'percent', 'values']" :key="i">
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
            <div class="p-6">
                <table class="w-full table-auto">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Channel</th>
                            <th v-if="approver" class="px-4 py-2"></th>
                            <!-- Data Columns -->
                            <th class="px-4 py-2" v-for="(column, i) in dataColumns" :key="i">
                                <span v-html="heading(column)"></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody :class="{'blur':loading}">
                        <tr v-for="(channel, i) in allForecastKeys" :key="i" class="hover:bg-yellow-50" :class="(i%2 == 0) ? 'bg-gray-50' : ''">
                            <!-- Channel name with status icon -->
                            <td class="channel-column border px-4 py-2">
                                <inertia-link :href="`/forecast/${program}/${channel}`">
                                    <span class="clickable channel-link">
                                        {{ channel }}
                                    </span>
                                </inertia-link>
                                <!-- Approved -->
                                <font-awesome-icon 
                                    icon="check-double" 
                                    v-if="forecast[channel] && forecast[channel].approved" 
                                    class="uk-text-success uk-margin-small-left" 
                                    v-vk-tooltip="'Approved ' + forecast[channel].approved" 
                                />
                                <!-- Submitted -->
                                <font-awesome-icon 
                                    icon="check" 
                                    v-else-if="forecast[channel]" 
                                    class="uk-text-primary uk-margin-small-left" 
                                    v-vk-tooltip="'Submitted ' + forecast[channel].updated" 
                                />
                            </td>
                            <!-- Approval icons -->
                            <td v-if="approver" class="border px-4 py-2">
                                <span v-if="forecast[channel] && forecast[channel].approved">
                                    <font-awesome-icon 
                                        icon="times-circle" 
                                        class="clickable auto-forecast-icon" 
                                        @click="disapprove(channel)" 
                                        v-vk-tooltip="'Remove Approval'"
                                    />
                                </span>
                                <span v-else-if="forecast[channel]" >
                                    <font-awesome-icon 
                                        icon="check-circle" 
                                        class="clickable uk-text-muted" 
                                        @click="approve(channel)" 
                                        v-vk-tooltip="'Approve Current Forecast'"
                                    />
                                </span>
                                <span v-else-if="! loading">
                                    <font-awesome-icon 
                                        icon="magic" 
                                        class="clickable auto-forecast-icon" 
                                        @click="autoForecast(channel)"
                                        v-vk-tooltip="'Submit Previous Forecast'" 
                                    />
                                </span>
                                <span v-else>
                                    <font-awesome-icon 
                                        icon="question-circle" 
                                        class="auto-forecast-icon"
                                    />
                                </span>
                            </td>
                            
                            <td v-for="(column, j) in dataColumns" :key="j" class="border px-4 py-2 text-right" :class="{'auto-forecast-icon' : cellData(column, channel) == 'n/a', 'between-year-month' : column == 'monthCpl'}">
                                <span v-html="cellData(column, channel)"></span>
                                <!-- <vk-drop class="uk-text-left" :delay-hide="100">
                                    <div>
                                        <span v-html="dropContents(column, channel)"></span>
                                    </div>
                                </vk-drop> -->
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button type="text" @click="approveAll()" v-if="approver && awaitingApproval.length > 0">
                    Approve All Forecasts
                </button>
            </div>
        </div>

        <program-totals 
            class="uk-margin-top uk-margin-bottom"
            :year="year"
            :month="month"
            :program="program"
            :forecast="forecast"
            :profitability="profitability"
        />

        <!-- <vk-modal :show.sync="showModal" size="container">
            <vk-modal-title>{{ program }} {{ modalChannel }} Current Forecast</vk-modal-title>
            <vk-modal-close @click="showModal = false"></vk-modal-close>
            <submit-current
                v-if="showModal"
                :program="program"
                :channel="modalChannel"
                @submitted="submitted()"
            />
        </vk-modal> -->
    </div>
</template>

<script>
import ProgramTotals from './ProgramTotals'
import DateMixins from '../../Mixins/Dates'

export default {
    name: 'forecast-program',

    mixins: [
        DateMixins
    ],

    components: {
        ProgramTotals,
    },

    props: {
        program: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            tableType: 'variance',
            ready: false,
            loadingForecast: true,
            loadingProfitability: true,
            showModal: false,
            modalChannel: '',
            profitability: {},
            forecast: {},
            settings: {},
            dataColumns: [
                'monthLeads',
                'monthSpend',
                'monthCpl',
                'yearLeads',
                'yearSpend',
                'yearCpl'
            ]
        }
    },

    computed: {
        profitabilityKeys() {
            return _.keys(this.profitability);
        },
        allForecastKeys() {
            let profitabilityKeys = this.profitabilityKeys;
            let forecastKeys = _.keys(this.forecast);
            let allKeys = _.concat(profitabilityKeys, forecastKeys);
            return _.uniq(allKeys);
        },
        awaitingApproval() {
            let array = [];
            _.forEach(this.forecast, (f, key) => {
                if (! f.approved) {
                    array.push(key);
                }
            });
            return array;
        },
        range() {
            return this.yearDigits(this.calendarYear);
        },
        loading() {
            if (! this.loadingForecast && ! this.loadingProfitability) {
                return false;
            }
            return true;
        },
        month() {
            return (this.settings.month) ? this.settings.month : '';
        },
        year() {
            return (this.settings.year) ? this.settings.year : '';
        },
        approver() {
            if (! this.myTeam == 'Media & Analytics') {
                return false;
            }
            if (this.myRole == 'Associate Director' || this.myRole == 'neXus Admin') {
                return true;
            }
            return false;
        },
        headingSuffix() {
            if (this.tableType == 'variance') {
                return '&Delta;';
            } else if (this.tableType == 'percent') {
                return '% &Delta;'
            } 
            return '';
        }
    },

    methods: {
        heading(column) {
            let string = '';
            if (column.includes('month')) {
                string = this.month + ' ' + this.year + '<br>';
            } else if (column.includes('year')) {
                string = 'Full Year ' + this.year + '<br>';
            }
            string += ' ';
            string += column.replace('month', '').replace('year', '');
            string += ' Forecast ';
            string += this.headingSuffix;
            return string;
        },
        channelIcon(channel) {

        },
        cellData(column, channel) {
            if (! this.forecast[channel]) {
                return 'n/a';
            }

            let type = this.tableType;
            let baseMetric = column.replace('month', '').replace('year', '').toLowerCase();
            if (column.includes('month')) {
                switch (baseMetric) {
                    case 'leads':
                    case 'spend':
                        let newValue = (this.forecast[channel]) ? this.forecast[channel][baseMetric][this.month] : 0;
                        let oldValue = (this.profitability[channel]) ? this.profitability[channel][baseMetric][this.month] : 0;
                        if (type == 'variance') {
                            let delta = newValue - oldValue;
                            return this.formatMetric(delta, baseMetric);
                        } else if (type == 'percent') {
                            let pct = (oldValue > 0) ? (newValue - oldValue) / oldValue : 0;
                            return this.formatMetric(pct, 'pct1');
                        }
                        return this.formatMetric(newValue, baseMetric);
                        break;
                    case 'cpl':
                        if (type == 'variance') {
                            return this.formatMetric(this.cplDelta(channel).delta, 'cpl');
                        } else if (type == 'percent') {
                            return this.formatMetric(this.cplDelta(channel).percent, 'pct1');
                        }
                        return this.formatMetric(this.cplDelta(channel).new, 'cpl');
                        break;
                    default:
                        break;
                }
            } else if (column.includes('year')) {
                switch (baseMetric) {
                    case 'leads':
                    case 'spend':
                        let newValue = (this.forecast[channel]) ? this.fullYear(this.forecast[channel][baseMetric]) : 0;
                        let oldValue = (this.profitability[channel]) ? this.fullYear(this.profitability[channel][baseMetric]) : 0;
                        if (type == 'variance') {
                            let delta = newValue - oldValue;
                            return this.formatMetric(delta, baseMetric);
                        } else if (type == 'percent') {
                            let pct = (oldValue > 0) ? (newValue - oldValue) / oldValue : 0;
                            return this.formatMetric(pct, 'pct1');
                        }
                        return this.formatMetric(newValue, baseMetric);
                        break;
                    case 'cpl':
                        if (type == 'variance') {
                            return this.formatMetric(this.cplFullYearDelta(channel).delta, 'cpl');
                        } else if (type == 'percent') {
                            return this.formatMetric(this.cplFullYearDelta(channel).percent, 'pct1');
                        }
                        return this.formatMetric(this.cplFullYearDelta(channel).new, 'cpl');
                        break;
                    default:
                        break;
                }
            }
        },
        dropContents(column, channel) {
            let baseMetric = column.replace('month', '').replace('year', '').toLowerCase();
            let budgetMetric = baseMetric + 'Budget';

            let newValue;
            let oldValue;
            let budgetValue;
            
            if (column == 'monthLeads' || column == 'monthSpend') {
                newValue = (this.forecast[channel]) ? this.formatMetric(this.forecast[channel][baseMetric][this.month], baseMetric) : 0;
                oldValue = (this.profitability[channel]) ? this.formatMetric(this.profitability[channel][baseMetric][this.month], baseMetric) : 0;
                budgetValue = (this.profitability[channel]) ? this.formatMetric(this.profitability[channel][budgetMetric][this.month], baseMetric) : 0;
            } else if (column == 'yearLeads' || column == 'yearSpend') {
                newValue = (this.forecast[channel]) ? this.formatMetric(this.fullYear(this.forecast[channel][baseMetric]), baseMetric) : 0;
                oldValue = (this.profitability[channel]) ? this.formatMetric(this.fullYear(this.profitability[channel][baseMetric]), baseMetric) : 0;
                budgetValue = (this.profitability[channel]) ? this.formatMetric(this.fullYear(this.profitability[channel][budgetMetric]), baseMetric) : 0;
            } else if (column == 'monthCpl') {
                newValue = this.formatMetric(this.cplDelta(channel).new, 'cpl');
                oldValue = this.formatMetric(this.cplDelta(channel).current, 'cpl');
            } else if (column == 'yearCpl') {
                newValue = this.formatMetric(this.cplFullYearDelta(channel).new, 'cpl');
                oldValue = this.formatMetric(this.cplFullYearDelta(channel).current, 'cpl');
            }

            let string = '';
            string += 'New Forecast: ';
            string += '<span class="uk-float-right">';
            string += newValue;
            string += '</span><br>';
            string += 'Prior Forecast: ';
            string += '<span class="uk-float-right">';
            string += oldValue;
            string += '</span>';
            if (budgetValue) {
                string += '<br>';
                string += 'Budget: ';
                string += '<span class="uk-float-right">';
                string += budgetValue;
                string += '</span>';
            }
            return string
        },
        cplDelta(channel) {
            let cpl = (this.forecast[channel] && this.forecast[channel].leads[this.month] > 0) ? this.forecast[channel].spend[this.month] / this.forecast[channel].leads[this.month] : 0;
            let currentCpl = (this.profitability[channel] && this.profitability[channel].leads[this.month]) ? this.profitability[channel].spend[this.month] / this.profitability[channel].leads[this.month] : 0;
            let obj = {
                new: Math.round(cpl),
                current: Math.round(currentCpl),
                delta: cpl - currentCpl,
            }
            obj.percent = (obj.current > 0) ? obj.delta / obj.current : 0;
            return obj;
        },
        cplFullYearDelta(channel) {
            let cpl = (this.forecast[channel] && this.fullYear(this.forecast[channel].leads) > 0) ? this.fullYear(this.forecast[channel].spend) / this.fullYear(this.forecast[channel].leads) : 0;
            let currentCpl = (this.profitability[channel] && this.fullYear(this.profitability[channel].leads) > 0) ? this.fullYear(this.profitability[channel].spend) / this.fullYear(this.profitability[channel].leads) : 0;
            let obj = {
                new: Math.round(cpl),
                current: Math.round(currentCpl),
                delta: cpl - currentCpl
            }
            obj.percent = (obj.current > 0) ? obj.delta / obj.current : 0;
            return obj;
        },
        drillDown(channel) {
            const router = this.$router;
            router.push('/forecast/' + this.program + '/' + channel);
        },
        fullYear(object) {
            let sum = 0;
            _.forEach(this.months, month => {
                sum += (object[month]) ? object[month] : 0;
            });
            return sum;
        },
        getProfitability() {
            this.loadingProfitability = true;
            let filter = {
                program: this.program,
                group: 'channel',
                metric: 'forecast'
            }
            let range = this.range;
            axios
                .get('/data/profitability/monthly', {
                    params: {
                        filter,
                        range
                    }
                })
                .then(({data}) => {
                    this.profitability = data;
                    this.loadingProfitability = false;
                });
        },
        getForecast() {
            this.loadingForecast = true;
            // this.forecast = {};
            axios
                .get('/data/forecast/program/' + this.program)
                .then(({data}) => {
                    this.forecast = data;
                    this.loadingForecast = false;
                });
        },
        refreshData() {
            this.getForecast();
            this.getProfitability();
            this.ready = true;
        },
        approve(channel) {
            axios
                .post('/api/forecast/approve/' + this.program + '/' + channel)
                .then(({data}) => {
                    console.log(data);
                    this.addMessage(channel + ' Forecast Approved');
                    this.getForecast();
                })
        },
        approveAll() {
            axios
                .post('/api/forecast/approve-program/' + this.program)
                .then(({data}) => {
                    console.log(data);
                    this.addMessage(this.program + ' All Forecasts Approved');
                    this.getForecast();
                })
        },
        disapprove(channel) {
            axios
                .post('/api/forecast/disapprove/' + this.program + '/' + channel)
                .then(({data}) => {
                    console.log(data);
                    this.addError(channel + ' Approval Removed');
                    this.getForecast();
                })
        },
        autoForecast(channel) {
            this.modalChannel = channel;
            this.showModal = true;
        },
        submitted() {
            this.showModal = false;
            this.modalChannel = '';
            this.getForecast();
        },

        changeChannel(channel) {
            const router = this.$router;
            router.push('/forecast/' + this.program + '/' + channel);
        },
    },

    watch: {
        program() {
            this.ready = false;
            this.refreshData();
        }
    },

    mounted() {
        this.refreshData();
    }
}
</script>

<style scoped>
.channel-link:hover {
    color: #1e87f0;
}
.between-year-month {
    border-right: 3px solid #d8d8d8;
}
.table-raise {
    margin-top: -20px;
}
.auto-forecast-icon {
    color: #e5e5e5;
    transition-duration: .3s;
}
.auto-forecast-icon:hover {
    color: #999;
}
.channel-column {
    width: 166px;
}
</style>