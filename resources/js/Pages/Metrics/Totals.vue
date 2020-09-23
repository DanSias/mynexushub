<template>
    <div>
        <portal to="MetricsTableTotals">
            <div divided class="flex space-between">
                <div v-for="(col, i) in columns" :key="i" class="flex-1 text-center" :class="(i > 0) ? 'border-l border-gray-200' : ''">
                    <span>
                        <!-- Heading with Icon -->
                        <span class="uk-text-small" @click="buildMetricChart(col)">
                            <button type="text" class="ul-text">
                                <span class="capitalize text-gray-600" v-html="metricHeading(col)" />
                            </button>
                        </span>

                        <!-- Large Metric Text -->
                        <h1 class="text-5xl nexus-blue font-semibold -mt-1">
                            <span v-if="col == 'spend'">
                                {{ bigMoney(tableTotals.spend) }}
                            </span>
                            <span v-else>
                                {{ formatMetric(tableTotals[col], col) }}
                            </span>
                        </h1>

                        <!-- Small Variance Text -->
                        <div v-show="tableComparisonPercent(col)" class="-mt-1">
                            <span v-if="loading" class="text-gray-500">Loading Forecasts...</span>
                            <span v-else :class="tableComparisonClass(col)">
                                <span class="">
                                    <span class="text-semibold">{{ tableComparisonPercent(col) }}% </span>
                                    <!-- <span v-if="tableComparisonIcon(col) == 'up'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    </span>
                                    <span v-else>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </span> -->
                                </span>
                            </span> 
                            <span v-show="! loading" v-html="comparisonText(col)"></span>
                        </div>
                    </span>

                    <!-- Popover -->
                    <!-- <vk-drop :offset="10" v-if="metricPopover(col).length > 0" :delay-hide="100">
                        <vk-card>
                            <ul class="uk-list">
                                <li v-for="(metric, index) in metricPopover(col)" :key="index">
                                    <span class="uk-float-left" v-html="metricHeading(metric)"></span>
                                    <div class="uk-float-right">
                                        <div class="">
                                            <span v-if="millions.includes(metric)">
                                                {{ bigMoney(tableTotals[metric], 2) }}
                                            </span>
                                            <span v-else>
                                                {{ formatMetric(tableTotals[metric], metric) }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </vk-card>
                    </vk-drop> -->
                </div>
            </div>
        </portal>
        
        <!-- <vk-modal :show.sync="showModal" size="container">
            <vk-modal-close @click="showModal = false" />
            <chart-modal 
                v-if="showModal"
                :metric="chartMetric" 
                :focus="''"
            />
        </vk-modal> -->
    </div>
</template>

<script>
// import ChartModal from './ChartModal';
import FormatMetric from '../../Mixins/FormatMetric'

export default {
    name: 'MetricsTotals',

    mixins: [
        FormatMetric
    ],

    components: {
        // ChartModal
    },

    props: {
        totals: {
            type: Object,
            default: function () {
                return {};
            }
        },
        list: {
            type: Array,
            default: function () {
                return [];
            }
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
        }
    },
    
    data() {
        return {
            showModal: false,
            chartMetric: 'Leads'
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
