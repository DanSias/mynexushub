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
                        <h1 class="text-4xl nexus-blue">
                            {{ prefix(metric) }}{{ totals[metric] | commas}}
                        </h1>
                        <p class="text-gray-600" @click="comparePercent = ! comparePercent">
                            <span v-if="comparePercent" data-balloon-pos="right" :aria-label="'Budget: ' + prefix(metric) + parseInt(budgetTotals[metric]).toLocaleString()">
                                {{ percentVariance(metric) | pct0 }}
                            </span>
                            <span v-else data-balloon-pos="right" :aria-label="'Budget: ' + prefix(metric) + parseInt(budgetTotals[metric]).toLocaleString()">
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
                    <div v-for="(metric, i) in ['leads', 'spend', 'cpl']" :key="i" class="text-center">
                        <span class="capitalize text-gray-500">
                            <!-- <font-awesome-icon :icon="icon(metric)" class="uk-margin-small-right" /> -->
                            {{ heading(metric) }}
                        </span>
                        <h1 class="text-4xl nexus-blue">
                            {{ prefix(metric) }}{{ totals[metric] | commas}}
                        </h1>
                        <p class="text-gray-600" @click="comparePercent = ! comparePercent" >
                            <span v-if="comparePercent" data-balloon-pos="right" :aria-label="'Budget: ' + prefix(metric) + parseInt(budgetTotals[metric]).toLocaleString()">
                                {{ percentVariance(metric) | pct0 }}
                            </span>
                            <span v-else data-balloon-pos="right" :aria-label="'Budget: ' + prefix(metric) + parseInt(budgetTotals[metric]).toLocaleString()">
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

export default {
    name: 'ForecastChannelTotals',

    props: {
        totals: {
            type: Object,
            default: function () {
                return {};
            }
        },
        budgetTotals: {
            type: Object,
            default: function () {
                return {};
            }
        },
        year: {
            type: Number,
            default: 0
        },
        month: {
            type: String,
            default: ''
        },
    },

    data() {
        return {
            comparePercent: true,
        }
    },

    methods: {
        heading(metric) {
            let text = metric.replace('month', '');
            if (text == 'cpl') {
                return 'CPL'
            }
            return text.toLowerCase();
        },
        icon(metric) {
            switch (metric) {
                case 'leads':
                case 'monthLeads':
                    return 'users';
                    break;
            
                case 'spend':
                case 'monthSpend':
                    return 'money-bill-alt';
                    break;

                default:
                    return 'balance-scale';
                    break;
            }
        },
        prefix(metric) {
            if (metric == 'monthLeads' || metric == 'leads') {
                return '';
            } else {
                return '$';
            }
        },
        variance(metric) {
            return parseInt(this.totals[metric] - this.budgetTotals[metric]);
        },
        varianceText(metric) {
            let delta = this.variance(metric);
            if (delta == 0) {
                return 'Even'
            }
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
            return (this.budgetTotals[metric] > 0) ? delta / this.budgetTotals[metric] : 0;
        },
        varianceIcon(metric) {
            return (this.variance(metric) > 0) ? 'caret-up' : 'caret-down';
        },
        varianceClass(metric) {
            let delta = this.variance(metric);
            if (delta == 0) {
                return 'text-gray-500';
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
.uk-text-bold {
    font-size: 1.2rem;
}
</style>