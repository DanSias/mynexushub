<template>
    <div>
        <portal to="MetricsTableTotalCards">
            <div class="flex flex-wrap">
              <div v-for="(col, i) in columns" :key="i"  class="w-full lg:w-6/12 xl:w-3/12 px-4">
                <div class="relative flex flex-col min-w-0 break-words bg-white rounded mb-6 xl:mb-0 shadow-lg">
                  <div class="flex-auto p-4">
                    <div class="flex flex-wrap items-center">
                      <div class="relative w-full pr-4 max-w-full flex-grow flex-1">
                        <h5 class="text-gray-500 uppercase font-bold text-xs tracking-wide">
                          {{ metricHeading(col) }}
                        </h5>
                        <span class="font-semibold text-3xl text-gray-800">
                          <span v-if="col == 'spend'">
                                {{ bigMoney(tableTotals.spend) }}
                            </span>
                            <span v-else>
                                {{ formatMetric(tableTotals[col], col) }}
                            </span>
                        </span>
                      </div>
                      <div class="relative w-auto pl-4 flex-initial">
                        <div @click="launchModal(col)" class="text-white p-3 text-center inline-flex items-center justify-center w-12 h-12 shadow-lg rounded-full bg-blue-500 hover:bg-blue-800 duration-300" :aria-label="`Launch ${metricHeading(col)} chart`" data-balloon-pos="down">
                            <span v-if="col.includes('leads')">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </span>
                            <span v-else-if="col.includes('spend')">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            <span v-else-if="col.includes('cpl')">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                            </span>
                            
                        </div>
                      </div>
                    </div>
                      <div class="text-sm text-gray-500 mt-4 flex">
                        <p v-if="tableComparisonPercent(col) > 0" :class="tableComparisonClass(col)" class="mr-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                        </p>
                        <p v-else-if="tableComparisonPercent(col) < 0" :class="tableComparisonClass(col)" class="mr-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        </p>
                        <p class="mr-1" :class="tableComparisonClass(col)">
                            <span>{{ tableComparisonPercent(col) }}% </span>
                            <span class="whitespace-no-wrap text-gray-500" v-html="comparisonText(col)"></span>
                        </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </portal>

        <BaseModal v-if="showModal" @close="showModal = false" scrollable :title="`${chartMetric} by Month`">
            <MetricChart 
                :filter="filter"
                :metric="chartMetric"
            />
            <p class="text-sm leading-5 text-gray-500">
            </p>
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
</template>

<script>
// import ChartModal from './ChartModal';
import BaseModal from '../../Components/BaseModal'
import FormatMetric from '../../Mixins/FormatMetric'
import MetricChart from './Chart'

export default {
    name: 'MetricsTotalCards',

    mixins: [
        FormatMetric
    ],

    components: {
        // ChartModal
        BaseModal,
        MetricChart
    },

    props: {
        totals: {
            type: Object,
            default: () => {}
        },
        list: {
            type: Array,
            default: () => []
        },
        comparison: {
            type: String,
            default: 'Budget'
        },
        pace: {
            type: Number,
            default: 1
        },
        loading: {
            type: Boolean,
            default: false
        },
        filter: {
            type: Object,
            default: () => {}
        }
    },
    
    data() {
        return {
            showModal: false,
            chartMetric: 'leads'
        }
    },

    computed: {

        columns() {
            switch (this.layout) {
                case 'acquisition':
                    return ['leads', 'spend', 'cpl'];
                    break;
                case 'acquisitionForecast':
                    return ['leads', 'spend', 'cpl'];
                    break;
                case 'leadBudget':
                    return ['leads', 'leadsBudgetPace', 'leadsBudget']
                    break;
                case 'leadForecast':
                    return ['leads', 'leadsForecastPace', 'leadsForecast']
                    break;
                case 'spendBudget':
                    return ['spend', 'spendBudget', 'spendBudgetDelta']
                    break;
                case 'spendForecast':
                    return ['spend', 'spendForecast', 'spendForecastDelta']
                    break;
                case 'cpl':
                    return ['cpl', 'cplBudget', 'cplBudgetDelta']
                    break;
                case 'cplForecast':
                    return ['cpl', 'cplForecast', 'cplForecastDelta']
                    break;
                default:
                    return ['leads', 'spend', 'cpl'];
                    break;
            }
        },
        tableTotals() {
            const sum = {
                leads: 0,
                spend: 0,
                leadsBudget: 0,
                leadsBudgetPace: 0,
                spendBudget: 0,
                leadsForecast: 0,
                leadsForecastPace: 0,
                spendForecast: 0
            }
            _.forEach(this.list, item => {
                sum.leads += this.totals[item].leads;
                sum.spend += this.totals[item].spend;
                sum.leadsBudget += this.totals[item].leadsBudget;
                sum.leadsBudgetPace += this.totals[item].leadsBudgetPace;
                sum.spendBudget += this.totals[item].spendBudget;
                sum.leadsForecast += (this.totals[item].leadsForecast) ? this.totals[item].leadsForecast : 0;
                sum.leadsForecastPace += (this.totals[item].leadsForecastPace) ? this.totals[item].leadsForecastPace : 0;
                sum.spendForecast += (this.totals[item].spendForecast) ? this.totals[item].spendForecast : 0;
            });
            sum.cpl = (sum.leads > 0) ? sum.spend / sum.leads : 0;
            sum.cplBudget = (sum.leadsBudget > 0) ? sum.spendBudget / sum.leadsBudget : 0;
            sum.cplForecast = (sum.leadsForecast > 0) ? sum.spendForecast / sum.leadsForecast : 0;
            sum.leadsBudgetPaceDelta = sum.leads - sum.leadsBudgetPace;
            sum.spendBudgetDelta = sum.spend - sum.spendBudget;
            sum.cplBudgetDelta = sum.cpl - sum.cplBudget;
            sum.leadsForecastPaceDelta = sum.leads - sum.leadsForecastPace;
            sum.spendForecastDelta = sum.spend - sum.spendForecast;
            sum.cplForecastDelta = sum.cpl - sum.cplForecast;
            return sum;
        }
    },

    methods: {
        launchModal(col) {
            this.chartMetric = col
            this.showModal = true
        },
        metricHeading(metric) {
            if (metric == 'cpl') {
                return 'CPL'
            }
            return metric
        },
        tableComparisonPercent(col) {
            if (this.comparison == 'Budget') {
                if (col == 'leads') {
                    let leads = this.tableTotals.leads;
                    let budget = this.tableTotals.leadsBudgetPace;
                    let diff = (leads - budget) / budget;
                    return (isNaN(diff)) ? 0 : Math.round(diff * 100);
                } else if (col == 'spend') {
                    let spend = this.tableTotals.spend;
                    let budget = this.tableTotals.spendBudget;
                    let diff = (spend - budget) / budget;
                    return (isNaN(diff)) ? 0 : Math.round(diff * 100);
                } else if (col == 'cpl') {
                    let cpl = this.tableTotals.cpl;
                    let budget = this.tableTotals.cplBudget;
                    let diff = (cpl - budget) / budget;
                    return (isNaN(diff)) ? 0 : Math.round(diff * 100);
                } else {
                    return false;
                }
            } else if (this.comparison == 'Forecast') {
                if (col == 'leads') {
                    let leads = this.tableTotals.leads;
                    let forecast = this.tableTotals.leadsForecastPace;
                    let diff = (leads - forecast) / forecast;
                    return (isNaN(diff)) ? 0 : Math.round(diff * 100);
                } else if (col == 'spend') {
                    let spend = this.tableTotals.spend;
                    let forecast = this.tableTotals.spendForecast;
                    let diff = (spend - forecast) / forecast;
                    return (isNaN(diff)) ? 0 : Math.round(diff * 100);
                } else if (col == 'cpl') {
                    let cpl = this.tableTotals.cpl;
                    let forecast = this.tableTotals.cplForecast;
                    let diff = (cpl - forecast) / forecast;
                    return (isNaN(diff)) ? 0 : Math.round(diff * 100);
                } else {
                    return false;
                }
            }
        },
        tableComparisonClass(col) {
            let percent = this.tableComparisonPercent(col);
            if (percent == 0) {
                return 'text-gray-500';
            }
            if (col == 'leads') {
                if (percent > 0) {
                    return 'text-green-500';
                } else {
                    return 'text-red-500';
                }
            } else {
                if (percent > 0) {
                    return 'text-red-500';
                } else {
                    return 'text-green-500';
                }
            }
        },
        tableComparisonIcon(col) {
            let percent = this.tableComparisonPercent(col);
            if (percent == 0) {
                return 'equals';
            }
            if (percent > 0) {
                return 'up';
            } else {
                return 'down';
            }
        },

        comparisonText(col) {
            let key = this.comparison;

            if (col == 'leads') {
                if (this.pace < 1) {
                    return ` to ${key} Pace`;
                } else {
                    return ` to ${key}`;
                }
            }
            return ' to ' + key;
        },

        metricPopover(metric) {
            if (this.comparison == 'Budget') {
                switch (metric) {
                    case 'leads':
                        return ['leadsBudgetPace', 'leadsBudgetPaceDelta'];
                        break;
                    case 'spend':
                        return ['spendBudget', 'spendBudgetDelta'];
                        break;
                    case 'cpl':
                        return ['cplBudget', 'cplBudgetDelta'];
                        break;
                    default:
                        return [];
                        break;
                }
            } else if (this.comparison == 'Forecast') {
                switch (metric) {
                    case 'leads':
                        return ['leadsForecastPace', 'leadsForecastPaceDelta'];
                        break;
                    case 'spend':
                        return ['spendForecast', 'spendForecastDelta'];
                        break;
                    case 'cpl':
                        return ['cplForecast', 'cplForecastDelta'];
                        break;
                    default:
                        return [];
                        break;
                }
            }
        },

        buildMetricChart(metric, item = '') {
            let charts = ['leads', 'spend', 'cpl'];
            if (! charts.includes(metric)) {
                return null;
            }
            this.chartMetric = metric;
            this.showModal = true;
        },
    }
}
</script>
