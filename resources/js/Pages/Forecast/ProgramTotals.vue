<template>
    <div class="mt-6 grid grid-cols-2 gap-6">
        <div class="bg-white rounded shadow">
            <p class="text-center capitalize border-b px-6 py-4 font-semibold text-gray-700 text-lg">{{ month  }} {{ year }}</p>
            <div class="p-6">
                <div class="grid grid-cols-3 gap-4">
                    <div v-for="(metric, i) in ['monthLeads', 'monthSpend', 'monthCpl']" :key="i" class="text-center">
                        <span class="text-gray-500" :class="(metric != 'cpl') ? 'capitalize' : ''">
                            <!-- <font-awesome-icon :icon="icon(metric)" class="uk-margin-small-right" /> -->
                            {{ heading(metric) }}
                        </span>
                        <h1 class="text-4xl" :class="(total[metric] != 0) ? 'nexus-blue' : 'text-gray-400'">
                            {{ prefix(metric) }}{{ total[metric] | commas}}
                        </h1>
                        <p class="text-gray-600" @click="comparePercent = ! comparePercent">
                            <span v-if="comparePercent" data-balloon-pos="right" :aria-label="'Budget: ' + prefix(metric) + parseInt(total[metric + 'Budget']).toLocaleString()" :class="varianceClass(metric)">
                                {{ percentVariance(metric) | pct0 }}
                            </span>
                            <span v-else data-balloon-pos="right" :aria-label="'Budget: ' + prefix(metric) + parseInt(total[metric + 'Budget']).toLocaleString()" :class="varianceClass(metric)">
                                {{ varianceText(metric) }}
                            </span>
                                to Budget
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Year -->
        <div class="bg-white rounded shadow">
            <p class="text-center capitalize border-b px-6 py-4 font-semibold text-gray-700 text-lg">Full Year {{ year }}</p>
            <div class="p-6">
                <div class="grid grid-cols-3 gap-4">
                    <div v-for="(metric, i) in ['yearLeads', 'yearSpend', 'yearCpl']" :key="i" class="text-center">
                        <span class="capitalize text-gray-500">
                            <!-- <font-awesome-icon :icon="icon(metric)" class="uk-margin-small-right" /> -->
                            {{ heading(metric) }}
                        </span>
                        <h1 class="text-4xl nexus-blue">
                            {{ prefix(metric) }}{{ total[metric] | commas}}
                        </h1>
                        <p class="text-gray-600" @click="comparePercent = ! comparePercent" >
                            <span v-if="comparePercent" data-balloon-pos="right" :aria-label="'Budget: ' + prefix(metric) + parseInt(total[metric + 'Budget']).toLocaleString()">
                                {{ percentVariance(metric) | pct0 }}
                            </span>
                            <span v-else data-balloon-pos="right" :aria-label="'Budget: ' + prefix(metric) + parseInt(total[metric + 'Budget']).toLocaleString()">
                                {{ varianceText(metric) }}
                            </span>
                            to Budget
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import MonthsMixin from '../../Mixins/Months'

export default {
    name: 'ForecastProgramTotals',

    mixins: [MonthsMixin],

    props: {
        month: {
            type: String,
            default: ''
        },
        year: {
            type: [Number, String],
            default: 0
        },
        program: {
            type: String,
            default: ''
        },
        forecast: {
            type: [Object, Array],
            default: function () {
                return {};
            }
        },
        profitability: {
            type: [Object, Array],
            default: function () {
                return {};
            }
        },
    },

    data() {
        return {
            comparePercent: true,
        }
    },

    computed: {
        total() {
            let obj = {
                monthLeads: 0,
                monthLeadsBudget: 0,
                monthSpend: 0,
                monthSpendBudget: 0,
                yearLeads: 0,
                yearLeadsBudget: 0,
                yearSpend: 0,
                yearSpendBudget: 0
            };
            let month = this.month;
            let months = this.months;

            _.forEach(this.forecast, f => {
                obj.monthLeads += (f.leads[month]) ? f.leads[month] : 0;
                obj.monthSpend += (f.spend[month]) ? f.spend[month] : 0;
                _.forEach(months, mo => {
                    obj.yearLeads += (f.leads[mo]) ? f.leads[mo] : 0;
                    obj.yearSpend += (f.spend[mo]) ? f.spend[mo] : 0;
                });
            });
            _.forEach(this.profitability, p => {
                obj.monthLeadsBudget += (p.leads[month]) ? p.leads[month] : 0;
                obj.monthSpendBudget += (p.spend[month]) ? p.spend[month] : 0;
                _.forEach(months, mo => {
                    obj.yearLeadsBudget += (p.leads[mo]) ? p.leads[mo] : 0;
                    obj.yearSpendBudget += (p.spend[mo]) ? p.spend[mo] : 0;
                });
            });
            // CPL
            obj.monthCpl = (obj.monthLeads > 0) ? obj.monthSpend / obj.monthLeads : 0;
            obj.monthCplBudget = (obj.monthLeadsBudget > 0) ? obj.monthSpendBudget / obj.monthLeadsBudget : 0;
            obj.yearCpl = (obj.yearLeads > 0) ? obj.yearSpend / obj.yearLeads : 0;
            obj.yearCplBudget = (obj.yearLeadsBudget > 0) ? obj.yearSpendBudget / obj.yearLeadsBudget : 0;
            return obj;
        }
    },

    methods: {
        heading(metric) {
            let text = metric.replace('month', '').replace('year', '');
            return text.toLowerCase();
        },
        icon(metric) {
            switch (metric) {
                case 'leads':
                case 'monthLeads':
                case 'yearLeads':
                    return 'users';
                    break;
            
                case 'spend':
                case 'monthSpend':
                case 'yearSpend':
                    return 'money-bill-alt';
                    break;

                default:
                    return 'balance-scale';
                    break;
            }
        },
        prefix(metric) {
            if (metric == 'monthLeads' || metric == 'yearLeads') {
                return '';
            } else {
                return '$';
            }
        },
        variance(metric) {
            let budgetMetric = metric + 'Budget';
            return parseInt(this.total[metric] - this.total[budgetMetric]);
        },
        varianceText(metric) {
            let delta = this.variance(metric);
            if (metric.toLowerCase().includes('leads')) {
                return delta.toLocaleString();
            } else if (delta >= 0) {
                return '$' + delta.toLocaleString();
            } else {
                return '-$' + Math.abs(delta).toLocaleString();
            }
        },
        percentVariance(metric) {
            let delta = this.variance(metric);
            return (this.total[metric] > 0) ? delta / this.total[metric] : 0;
        },
        varianceIcon(metric) {
            return (this.variance(metric) > 0) ? 'caret-up' : 'caret-down';
        },
        varianceClass(metric) {
            let delta = this.variance(metric);
            if (delta == 0) {
                return 'uk-text-muted';
            }
            if (metric == 'leads' || metric == 'monthLeads') {
                return (delta > 0) ? 'text-green-500' : 'text-red-500';
            } else {
                return (delta > 0) ? 'text-red-500' : 'text-green-500';
            }
        }
    },
}
</script>

<style scoped>
.range-text {
    font-size: 1.3rem;
}
</style>    