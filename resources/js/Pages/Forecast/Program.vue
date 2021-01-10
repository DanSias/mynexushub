<template>
    <div>
        <ProgramTotals 
            class=""
            :year="year"
            :month="month"
            :program="program"
            :forecast="forecast"
            :profitability="profitability"
        />
        <div class="bg-white mt-8 rounded shadow w-full">
            <div class="uk-margin-top uk-margin-bottom" v-show="ready">
                <div class="px-6 py-4 flex justify-between border-b border-gray-300">
                    <div class="flex">
                        New Forecast Compared to the Existing Forecast
                        <span ref="trigger" v-on:mouseenter="togglePopover()" v-on:mouseleave="togglePopover()"  class="ml-4 text-gray-300 hover:text-gray-600 duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </span>
                        <div 
                            ref="pop" 
                            :class="(showPopover) ? 'block' : 'hidden'" 
                            class="border border-gray-200 mt-1 block z-50 text-sm shadow-xl text-left no-underline break-words"
                        >
                            <div>
                                <div class="bg-gray-50 text-gray-800 font-semibold p-3 mb-0 border-b border-solid border-gray-200 capitalize">
                                    Progress Table
                                </div>
                                <div class="bg-white p-6 max-w-lg">
                                    <p>Leads, Spend, And CPL Differences by channel to compare this new <span class="capitalize">{{ month }}</span> forecast to the forecast that was submitted last month.</p>
                                    <p class="mt-3">The left side of the table shows the variance for the upcoming month, and the right half compares the entire year. Hover over any value</p>
                                    <p class="mt-3">Any row with <span class="text-gray-400">n/a</span> means that channel has not yet submitted a forecast.</p>
                                    <p class="mt-3">Click on a channel to view initiative level inputs.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex">
                        <div v-for="(type, i) in ['variance', 'percent', 'values']" :key="i">
                            <span 
                                class="capitalize ml-4 cursor-pointer py-1 px-4 rounded-full" 
                                :class="(tableType == type) ? 'bg-gray-900 text-white  ' : 'text-gray-800 hover:bg-gray-100' "
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
                            <tr class="text-gray-600 align-bottom">
                                <th class="px-4 py-2">Channel</th>
                                <th class="px-4 py-2">&nbsp;</th>
                                <!-- Data Columns -->
                                <th class="px-4 py-2 capitalize" v-for="(column, i) in dataColumns" :key="i">
                                    <span v-html="heading(column)"></span>
                                </th>
                            </tr>
                        </thead>
                        <tbody :class="{'blur':loading}">
                            <tr v-for="(channel, i) in allForecastKeys" :key="i" class="hover:bg-yellow-50" :class="(i%2 == 0) ? 'bg-gray-50' : ''">
                                <!-- Channel name with status icon -->
                                <td class="border px-4 py-2">
                                    <span class="w-full flex justify-between">
                                        <inertia-link :href="`/forecast/${program}/${channel}`">
                                            <span class="clickable channel-link">
                                                {{ channel }}
                                            </span>
                                        </inertia-link>
                                        <!-- Approved -->
                                        <span
                                            v-if="forecast[channel] && forecast[channel].approved" 
                                            class="text-green-500 font-semibold" 
                                            :aria-label="'Approved ' + forecast[channel].approved" 
                                            data-balloon-pos="right"
                                        >
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                                        </span>
                                        <!-- Submitted -->
                                        <span 
                                            v-else-if="forecast[channel]" 
                                            class="text-blue-500 font-semibold" 
                                            :aria-label="'Submitted ' + forecast[channel].updated" 
                                            data-balloon-pos="right"
                                        >
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </span>
                                    </span>
                                </td>
                                <!-- Approval icons -->
                                <td class="border px-4 py-2">
                                    <span 
                                        v-if="forecast[channel] && forecast[channel].approved" 
                                        @click="disapprove(channel)" 
                                        class="text-gray-300 hover:text-gray-600 duration-200"
                                        :aria-label="`Remove ${channel} Approval`"
                                        data-balloon-pos="left"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </span>
                                    <span 
                                        v-else-if="forecast[channel]" 
                                        @click="approve(channel)" 
                                        class="text-gray-300 hover:text-gray-600 duration-200"
                                        :aria-label="`Approve ${channel} Forecast`"
                                        data-balloon-pos="left"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                                    </span>
                                    <span v-else-if="! loading" 
                                        @click="autoForecast(channel)"
                                        class="text-gray-300 hover:text-gray-600 duration-200"
                                        :aria-label="`Submit Previous ${channel} Forecast`" 
                                        data-balloon-pos="left"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                    </span>
                                </td>
                                
                                <td v-for="(column, j) in dataColumns" :key="j" class="border px-4 py-2 text-right" :class="{'auto-forecast-icon' : cellData(column, channel) == 'n/a', 'between-year-month' : column == 'monthCpl'}">
                                    <span v-html="cellData(column, channel)" :class="(cellData(column, channel) == 'n/a') ? 'text-gray-300': 'text-gray-800'"></span>
                                    <!-- <vk-drop class="uk-text-left" :delay-hide="100">
                                        <div>
                                            <span v-html="dropContents(column, channel)"></span>
                                        </div>
                                    </vk-drop> -->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button class="bg-gray-800 text-white rounded px-4 py-2 mx-2 mt-6 mb-1 hover:bg-blue-500 duration-200 focus:outline-none" @click="approveAll()" v-if="approver && awaitingApproval.length > 0">
                        Approve All Forecasts
                    </button>
                </div>
            </div>


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

            <!-- Feedback and Help Modal -->

            <BaseModal v-if="showModal" @close="showModal = false" scrollable :title="modalChannel + ' Current Forecast'">
                <div>
                    <Resubmit
                        :channel="modalChannel"
                        :program="program"
                        @submitted="getForecast(); showModal = false;"
                    />
                </div>
                <template v-slot:footer>
                    <!-- <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                        <button
                            type="button"
                            class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-red-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                        >
                            Deactivate
                        </button>
                    </span> -->
                </template>
            </BaseModal>
        </div>
    </div>
</template>

<script>
import Popper from "popper.js";
import ProgramTotals from './ProgramTotals'
import DateMixins from '../../Mixins/Dates'
import FormatMetric from '../../Mixins/FormatMetric'
import Resubmit from './Resubmit';
import BaseModal from '../../Components/BaseModal'

export default {
    name: 'forecast-program',

    mixins: [
        DateMixins,
        FormatMetric
    ],

    components: {
        ProgramTotals,
        Resubmit,
        BaseModal
    },

    props: {
        program: {
            type: String,
            default: ''
        },
        approver: {
            type: Boolean,
            default: false
        },
        month: {
            type: String,
            default: ''
        },
        year: {
            type: Number,
            default: 0
        }
    },

    data() {
        return {
            tableType: 'variance',
            ready: false,
            loadingForecast: true,
            loadingProfitability: true,
            showPopover: false,
            showModal: false,
            modalChannel: '',
            profitability: {},
            forecast: {},
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
        addMessage(msg) {
            console.log(msg)
        },
        addError(msg) {
            console.log(msg)
        },
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
                .post('/data/forecast/approve/' + this.program + '/' + channel)
                .then(({data}) => {
                    console.log(data);
                    this.addMessage(channel + ' Forecast Approved');
                    this.getForecast();
                })
        },
        approveAll() {
            axios
                .post('/data/forecast/approve-program/' + this.program)
                .then(({data}) => {
                    console.log(data);
                    this.addMessage(this.program + ' All Forecasts Approved');
                    this.getForecast();
                })
        },
        disapprove(channel) {
            axios
                .post('/data/forecast/disapprove/' + this.program + '/' + channel)
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
