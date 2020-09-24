<template>
    <div>
        <div divided class="uk-child-width-expand@s">
            <div v-for="(col, index) in ['leads', 'spend', 'cpl']" :key="index" class="grid-item uk-text-center">
                <span class="metric-item">
                    <!-- Metric and Icon -->
                    <span class="uk-text-small">
                        <font-awesome-icon class="uk-text-primary metric-icon fa-fw" :icon="metricIcon[col]" /> 
                        <vk-button type="text" class="ul-text">
                            {{ col }}
                        </vk-button>
                    </span>

                    <!-- Large Metric Text -->
                    <h1 class="uk-heading-primary uk-margin-remove uk-text-light uk-text-primary">
                        <span v-if="col != 'leads'">$</span>{{ total[col] | commas}}
                    </h1>

                    <!-- Small Variance Text -->
                    <div 
                        class="uk-text-small uk-text-truncate" 
                    >
                        <span :class="metricBudgetClass(col)">
                            {{ metricBudgetPercent(col) }}% <font-awesome-icon class="fa-fw" :icon="metricBudgetIcon(col)" />
                        </span> 
                        <span>to Budget</span>
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
                                            {{ bigMoney(tableTotal(metric), 2) }}
                                        </span>
                                        <span v-else>
                                            {{ formatMetric(tableTotal(metric), metric) }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </vk-card>
                </vk-drop> -->
            </div>
        </div>

        <ul class="uk-tab uk-child-width-expand edit-modal-tabs uk-margin-large-top">
            <li 
                v-for="(m, index) in ['leads', 'spend', 'cpl']" 
                :key="index"
                :class="{'uk-active': m == metric }"
            >
                <a href="#" @click.prevent="metric = m"><font-awesome-icon :icon="metricIcon[m]" /> &nbsp; {{ m }}</a>
            </li>
        </ul>
        <!-- Table -->
        <table v-if="metric" class="uk-table uk-table-striped uk-table-hover uk-table-small uk-table-middle nexus-table">
            <thead>
                <tr>
                    <th></th>
                    <th v-for="(month, index) in months" :key="index" class="uk-text-center">
                        {{ month.slice(0, 3) }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Forecast</td>
                    <td v-for="(month, index) in months" :key="index" class="uk-text-right">
                        <span v-if="metric != 'leads'">$</span>{{ metricForecast[month] | bigCommas }}
                    </td>
                </tr>
                <tr>
                    <td>Budget</td>
                    <td v-for="(month, index) in months" :key="index" class="uk-text-right">
                        <span v-if="metric != 'leads'">$</span>{{ metricBudget[month] | bigCommas }}
                    </td>
                </tr>
                <tr>
                    <td>Variance</td>
                    <td v-for="(month, index) in months" :key="index" class="uk-text-right">
                        <span v-if="metric != 'leads'">$</span>{{ metricForecast[month] - metricBudget[month] | bigCommas }}
                    </td>
                </tr>
            </tbody>
        </table> 
    </div>
</template>

<script>
export default {
    name: 'forecast-initiative-chart',

    props: {
        initiative: {
            type: String,
            default: '',
        },
        profitability: {
            type: Object,
            default: function () {
                return {};
            },
        },
        forecast: {
            type: Object,
            default: function () {
                return {};
            },
        },
        months: {
            type: Array,
            default: function () {
                return [];
            },
        }
    },

    data() {
        return {
            metric: 'leads',
            metricIcon: {
                leads: 'users',
                spend: 'money-bill-alt',
                cpl: 'balance-scale'
            }
        }
    },

    computed: {
        total() {
            let obj = {
                leads: 0,
                spend: 0,
                cpl: 0
            };
            _.forEach(this.months, month => {
                obj.leads += this.initiativeForecast.leads[month];
                obj.spend += this.initiativeForecast.spend[month];
            });
            obj.cpl = (obj.leads > 0) ? Math.round(obj.spend / obj.leads) : 0;
            return obj;
        },
        budgetTotal() {
            let obj = {
                leads: 0,
                spend: 0,
                cpl: 0
            };
            _.forEach(this.months, month => {
                obj.leads += this.initiativeProfitability.leadsBudget[month];
                obj.spend += this.initiativeProfitability.spendBudget[month];
            });
            obj.cpl = (obj.leads > 0) ? Math.round(obj.spend / obj.leads) : 0;
            return obj;
        },
        initiativeProfitability() {
            return this.profitability[this.initiative];
        },
        initiativeForecast() {
            return this.forecast[this.initiative];
        },
        metricBudget() {
            let budgetText = this.metric + 'Budget';
            return (this.initiativeProfitability[budgetText]) ? this.initiativeProfitability[budgetText] : {};
        },
        metricBudgetArray() {
            return _.values(this.metricBudget);
        },
        metricForecast() {
            if (this.metric == 'cpl') {
                let obj = {};
                _.forEach(this.months, month => {
                    obj[month] = (this.initiativeForecast.leads[month] > 0) ? this.initiativeForecast.spend[month] / this.initiativeForecast.leads[month] : 0;
                })
                return obj;
            } else {
                return this.initiativeForecast[this.metric];
            }
        },
    },

    methods: {
        metricBudgetPercent(metric) {
            if (metric == 'leads') {
                let leads = this.total.leads;
                let budget = this.budgetTotal.leads;
                let diff = (leads - budget) / budget;
                return (isNaN(diff)) ? 0 : Math.round(diff * 100);
            } else if (metric == 'spend') {
                let spend = this.total.spend;
                let budget = this.budgetTotal.spend;
                let diff = (spend - budget) / budget;
                return (isNaN(diff)) ? 0 : Math.round(diff * 100);
            } else if (metric == 'cpl') {
                let cpl = this.total.cpl;
                let budget = this.budgetTotal.cpl;
                let diff = (cpl - budget) / budget;
                return (isNaN(diff)) ? 0 : Math.round(diff * 100);
            } else {
                return 0;
            }
        },
        metricBudgetClass(metric) {
            let percent = this.metricBudgetPercent(metric);
            if (percent == 0) {
                return 'uk-text-muted';
            }
            if (metric == 'leads') {
                if (percent > 0) {
                    return 'uk-text-success';
                } else {
                    return 'uk-text-danger';
                }
            } else {
                if (percent > 0) {
                    return 'uk-text-danger';
                } else {
                    return 'uk-text-success';
                }
            }
        },
        metricBudgetIcon(metric) {
            let percent = this.metricBudgetPercent(metric);
            if (percent == 0) {
                return 'equals';
            }
            if (percent > 0) {
                return 'caret-up';
            } else {
                return 'caret-down';
            }
        },
    },
}
</script>
