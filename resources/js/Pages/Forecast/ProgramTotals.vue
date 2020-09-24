<template>
    <div v-show="month && total.monthLeads > 0">
        <h3 class="uk-text-center">{{ program }} Forecast Totals</h3>
        
        <div divided class="uk-child-width-1-2">
            <div class="uk-text-center">
                <div>
                    <div slot="header">
                        <p class="capitalize range-text uk-margin-remove-bottom">{{ month }} {{ year}}</p>
                    </div>
                    <div class="uk-child-width-1-3">
                        <div v-for="(metric, i) in ['monthLeads', 'monthSpend', 'monthCpl']" :key="i">
                            <span class="uk-text-muted capitalize">
                                <!-- <font-awesome-icon :icon="icon(metric)" class="uk-margin-small-right" /> -->
                                {{ heading(metric) }}
                            </span>
                            <h1 class="uk-text-primary uk-margin-remove-top uk-margin-remove-bottom">
                                {{ prefix(metric) }}{{ total[metric] | commas }}
                            </h1>
                            <p class="uk-margin-remove-top" @click="comparePercent = ! comparePercent" data-balloon-pos="down" :aria-label="'Budget: ' + prefix(metric) + parseInt(total[metric + 'Budget']).toLocaleString()">
                                <span v-if="comparePercent">
                                    {{ percentVariance(metric) | pct1 }}
                                </span>
                                <span v-else>
                                    {{ varianceText(metric) }}
                                </span>
                                <!-- <font-awesome-icon :icon="varianceIcon(metric)" :class="varianceClass(metric)" /> to Budget -->
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="uk-text-center">
                <div>
                    <div slot="header">
                        <p class="range-text uk-margin-remove-bottom">Full Year {{ year }}</p>
                    </div>
                    <div class="uk-child-width-1-3">
                        <div v-for="(metric, i) in ['yearLeads', 'yearSpend', 'yearCpl']" :key="i">
                            <span class="uk-text-muted capitalize">
                                <!-- <font-awesome-icon :icon="icon(metric)" class="uk-margin-small-right" /> -->
                                {{ heading(metric) }}
                            </span>
                            <h1 class="uk-text-primary uk-margin-remove-top uk-margin-remove-bottom">
                                {{ prefix(metric) }}{{ total[metric] | commas }}
                            </h1>
                            <p class="uk-margin-remove-top" @click="comparePercent = ! comparePercent" data-balloon-pos="down" :aria-label="'Budget: ' + prefix(metric) + parseInt(total[metric + 'Budget']).toLocaleString()">
                                <span v-if="comparePercent">
                                    {{ percentVariance(metric) | pct1 }}
                                </span>
                                <span v-else>
                                    {{ varianceText(metric) }}
                                </span>
                                <!-- <font-awesome-icon :icon="varianceIcon(metric)" :class="varianceClass(metric)" /> to Budget -->
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'ForecastProgramTotals',

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
                return (delta > 0) ? 'uk-text-success' : 'uk-text-danger';
            } else {
                return (delta > 0) ? 'uk-text-danger' : 'uk-text-success';
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