<template>
    <div>
        <div class="mt-6 bg-white rounded shadow">
            <div class="flex justify-between px-6 py-4 border-b border-gray-200">
                <div class="flex card-header-text" :class="{'blur' : loading }">
                    <span @click="showModal(init)">{{ init }}</span>
                    <!-- Buttons to Adjust leads and Spend -->
                    <span class="flex text-sm ml-20 text-gray-400 ">
                        <!-- Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>

                        <div class="border-r border-gray-200 px-2">
                            <span 
                                @click="setAdjustmentMetric('leads')" 
                                class="px-3 py-1 duration-150 rounded" 
                                :class="(adjustMetric == 'leads') ? 'bg-gray-800 text-white' : 'bg-white hover:bg-gray-50 text-gray-800'" 
                                data-balloon-pos="left" 
                                aria-label="Adjust Lead Inputs"
                            >
                                Leads
                            </span>
                        </div>

                        <div class="pl-2">
                            <span 
                                @click="setAdjustmentMetric('spend')" 
                                class="px-3 py-1 duration-150 rounded" 
                                :class="(adjustMetric == 'spend') ? 'bg-gray-800 text-white' : 'bg-white hover:bg-gray-100 text-gray-800'" 
                                data-balloon-pos="right" 
                                aria-label="Adjust Spend Inputs"
                            >
                                Spend
                            </span>
                        </div>
                    </span>
                </div>

                <!-- Initiative Totals w/ Icons -->
                <span class="flex text-gray-800" :class="{'blur': loading, 'uk-text-muted': ! loading}">
                    <div class="flex" data-balloon-pos="left" :aria-label="`Budget: ${initTotal.leadsBudget.toLocaleString()}\nVariance: ${initVarianceString('leads')}`" data-balloon-break>
                        <svg class="text-gray-400 user-group w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span class="ml-3">{{ initTotal.leads | commas }}</span>
                    </div>

                    <span class="mx-6 text-gray-200">|</span>
                    <!-- Spend -->
                    <span class="flex" data-balloon-pos="left" :aria-label="`Budget: $${initTotal.spendBudget.toLocaleString()}\nVariance: ${initVarianceString('spend')}`" data-balloon-break>
                        <svg class="text-gray-400 currency-dollar w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="ml-3">${{ initTotal.spend | commas }}</span>
                    </span>

                    <span class="mx-6 text-gray-200">|</span>
                    <!-- CPL -->
                    <span class="flex" data-balloon-pos="left" :aria-label="`Budget: $${initCplBudget(init).toLocaleString()}\nVariance: ${initVarianceString('cpl')}`" data-balloon-break>
                        <svg class="text-gray-400 scale w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        <span class="ml-3">${{ initCpl(init) | commas }}</span>
                    </span>
                </span>
            </div>

            <div>
                <!-- Adjustment Inputs -->
                <div v-if="adjustMetric" class="mt-6">
                    <div class="w-1/3 mx-8 mb-4 content-center">
                        <div class="p-6 mx-2 flex justify-center border-b border-gray-200">
                            <!-- List actions -->
                            <div v-for="(a, i) in actions" :key="i">
                                <div 
                                    class="capitalize text-center px-3 py-1 cursor-pointer mx-2 duration-300 font-semibold"
                                    :class="(a == action) ? 'border-b border-blue-500 text-blue-500' : 'bg-white text-gray-800 hover:bg-gray-50'"
                                    @click="action = a"
                                >
                                    {{ a }}
                                </div>
                            </div>
                            <div class="text-gray-300 hover:text-gray-600" data-balloon-pos="right" :aria-label="`Hello There`">
                                <svg class="question-circle w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>

                        </div>
                        <div class="border border-gray-300 rounded px-2 py-1">
                            <input type="number" placeholder="Amount" v-model="amount">
                        </div>
                        <div class="flex -mx-2 py-3">
                            <button class="mx-2 capitalize w-1/2 bg-gray-800 text-white rounded px-4 py-2 hover:bg-blue-500  duration-300 focus:outline-none" @click="calculateAction('month')">
                                {{ month }} 2020
                            </button>
                            <button class="mx-2 capitalize w-1/2 bg-gray-800 text-white rounded px-4 py-2 hover:bg-blue-500  duration-300 focus:outline-none" @click="calculateAction('all')">
                                All Remaining Months
                            </button>
                        </div>
                    </div>
                    <hr>
                </div>

                <!-- Table with Inputs -->
                <div class="px-6 py-4" v-if="! collapseAll">
                    <table class="table-fixed" v-if="initiativeData">
                        <thead>
                            <tr class="font-thin text-sm uppercase tracking-wide">
                                <th class="px-4 py-2">&nbsp;</th>
                                <th 
                                    v-for="(month, i) in months" :key="i" 
                                    class="text-center px-4 py-2" 
                                    :class="(forecastMonths.includes(month)) ? 'text-gray-500' : 'text-gray-400'"
                                >
                                    {{ month.substring(0, 3) }}
                                </th>
                            </tr>
                        </thead>
                        <tbody :class="{'blur': loading}">
                            <!-- Leads -->
                            <tr v-if="initiativeData['leads']" class="bg-gray-50 hover:bg-yellow-50">
                                <td class="border px-4 py-2">Leads</td>
                                <td v-for="(month, i) in months" :key="i" class="text-right border px-3 py-2">
                                    <span v-if="forecastMonths.includes(month)">
                                        <input class="forecast-input" v-model="initiativeData['leads'][month]" type="number" min="0" v-show="! loading">
                                    </span>
                                    <span v-else class="mr-2 text-gray-400">
                                        {{ formatMetric(initiativeData['leads'][month], 'leads') }}
                                    </span>
                                </td>
                            </tr>
                            <!-- Spend -->
                            <tr v-if="initiativeData['spend']" class=" hover:bg-yellow-50">
                                <td class="border px-4 py-2">Spend</td>
                                <td v-for="(month, i) in months" :key="i" class="text-right border px-3 py-2">
                                    <span v-if="forecastMonths.includes(month)">
                                        <input class="forecast-input" v-model="initiativeData['spend'][month]" type="number" min="0" v-show="! loading">
                                    </span>
                                    <span v-else class="mr-2 text-gray-400">
                                        {{ formatMetric(initiativeData['spend'][month], 'spend') }}
                                    </span>
                                </td>
                            </tr>
                            <!-- CPL -->
                            <tr v-if="initiativeData['spend'] && initiativeData['leads']" class="bg-gray-50 hover:bg-yellow-50">
                                <td class="border px-4 py-2">CPL</td>
                                <td v-for="(month, i) in months" :key="i" class="text-right border px-3 py-2">
                                    <span v-if="forecastMonths.includes(month)">
                                        <input class="forecast-input" :value="cpl(month)" @change=" event => cplChange(month, event)" type="number" min="0"    v-show="! loading">
                                    </span>
                                    <span v-else class="mr-2 text-gray-400">
                                        {{ formatMetric(cpl(month), 'cpl') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import FormatMetric from '../../Mixins/FormatMetric'

export default {
    name: 'ForecastInitiative',

    mixins: [FormatMetric],
    
    props: {
        init: {
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
        },
        month: {
            type: String,
            default: ''
        },
        months: {
            type: Array,
            default: () => []
        },
        forecastMonths: {
            type: Array,
            default: () => []
        },
        loading: {
            type: Boolean,
            default: false
        },
        profitability: {
            type: Object,
            default: () => {}
        },
        initiativeData: {
            type: Object,
            default: () => {}
        },
        collapseAll: {
            type: Boolean,
            default: false
        },
    },

    data() {
        return {
            action: 'add',
            actions: ['add', 'scale', 'distribute'],
            amount: 0,
            adjustMetric: '',
        }
    },

    computed: {
        initTotal() {
            let sum = {
                leads: 0,
                spend: 0,
                leadsBudget: 0,
                spendBudget: 0
            }
            let metrics = ['leads', 'spend', 'leadsBudget', 'spendBudget']
            _.forEach(metrics, metric => {
                // sum[metric] = 0
                if (metric.includes('Budget')) {
                    _.forEach(this.months, month => {
                        if (this.profitability && this.profitability[metric] && this.profitability[metric][month]) {
                            sum[metric] += parseInt(this.profitability[metric][month]);
                        }
                    });
                } else {
                    _.forEach(this.months, month => {
                        if (this.initiativeData && this.initiativeData[metric][month]) {
                            sum[metric] += parseInt(this.initiativeData[metric][month]);
                        }
                    });
                }
            })

            return sum
        }
    },

    methods: {
        
        setAdjustmentMetric(metric) {
            if (this.adjustMetric == metric) {
                this.adjustMetric = '';
            } else {
                this.adjustMetric = metric;
            }
        },


        calculateAction(dist) {
            let action = this.action
            let amount = this.amount

            switch (action) {
                case 'add':
                    this.addMetric(dist)
                    break;
                case 'scale':
                    this.scaleMetric(dist)
                    break;
                case 'distribute':
                    this.distMetric(dist)
                    break;
            
                default:
                    break;
            }
        },
        initCpl() {
            let spend = this.initTotal.spend;
            let leads = this.initTotal.leads;
            let cpl = (leads > 0) ? spend / leads : 0;
            return cpl;
        },
        initCplBudget() {
            let spend = this.initTotal.spendBudget;
            let leads = this.initTotal.leadsBudget;
            let cpl = (leads > 0) ? Math.round(spend / leads) : 0;
            return cpl;
        },
        initVariance(metric) {
            let base;
            let budget;
            if (metric == 'cpl') {
                base = this.initCpl(init);
                budget = this.initCplBudget(init);
            } else {
                base = this.initTotal(metric);
                budget = this.initTotal(metric + 'Budget');
            }
            return parseInt(base - budget);
        },
        initVarianceString(metric) {
            let value = this.initVariance(metric)
            if (metric == 'leads') {
                return value.toLocaleString()
            }
            if (value > 0) {
                return '$' + value.toLocaleString()
            }
            return '-$' + Math.abs(value).toLocaleString()
        },
        initPercentVariance(metric) {
            let budget;
            if (metric == 'cpl') {
                budget = this.initCplBudget(init);
            } else {
                budget = this.initTotal(metric + 'Budget');
            }
            let delta = this.initVariance(metric);
            return (budget > 0) ? delta / budget : 0;
        },
        initPercentClass(metric) {
            let pct = this.initPercentVariance(metric);
            if (metric == 'leads') {
                if (pct > 0.1) {
                    return 'uk-text-success';
                } else if (pct < -0.1) {
                    return 'uk-text-danger';
                }
            } else {
                if (pct > 0.1) {
                    return 'uk-text-danger';
                } else if (pct < -0.1) {
                    return 'uk-text-success';
                }
            }
            return 'uk-text-muted';
        },



        initVariance(metric, init) {
            let base;
            let budget;
            if (metric == 'cpl') {
                base = this.initCpl(init);
                budget = this.initCplBudget(init);
            } else {
                base = this.initTotal[metric]
                budget = this.initTotal[metric + 'Budget']
            }
            return parseInt(base - budget);
        },
        initVarianceString(metric) {
            let value = this.initVariance(metric)
            if (metric == 'leads') {
                return value.toLocaleString()
            }
            if (value > 0) {
                return '$' + value.toLocaleString()
            }
            return '-$' + Math.abs(value).toLocaleString()
        },
        initPercentVariance(metric) {
            let budget;
            if (metric == 'cpl') {
                budget = this.initCplBudget(init);
            } else {
                budget = this.initTotal[metric + 'Budget']
            }
            let delta = this.initVariance[metric]
            return (budget > 0) ? delta / budget : 0;
        },
        initPercentClass(metric) {
            let pct = this.initPercentVariance(metric);
            if (metric == 'leads') {
                if (pct > 0.1) {
                    return 'uk-text-success';
                } else if (pct < -0.1) {
                    return 'uk-text-danger';
                }
            } else {
                if (pct > 0.1) {
                    return 'uk-text-danger';
                } else if (pct < -0.1) {
                    return 'uk-text-success';
                }
            }
            return 'uk-text-muted';
        },

        cpl(month) {
            let leads = 0;
            let spend = 0;
            let cpl = 0;

            if (this.initiativeData['leads'][month]) {
                leads = this.initiativeData['leads'][month];
            }
            if (this.initiativeData['spend'][month]) {
                spend = this.initiativeData['spend'][month];
            }
            if (leads > 0) {
                cpl = Math.round(spend / leads);
            }
            return cpl;
        },
        cplChange(month, event) {
            let cpl = event.target.value;
            let spend = this.initiativeData['spend'][month];
            let newLeads = spend / cpl;
            this.initiativeData['leads'][month] = Math.round(newLeads);

            console.log(`update CPL to ${cpl} for ${init} in the month of ${month}`);
        },


        addMetric(timeframe) {
            let add = parseInt(this.amount);
            let metric = this.adjustMetric;
            let init = this.adjustInitiative;
            if (timeframe == 'month') {
                let month = this.month;
                this.initiativeData[init][metric][month] += add;
            } else if (timeframe == 'all') {
                _.forEach(this.forecastMonths, month => {
                    this.initiativeData[init][metric][month] += add;
                })
            }
        },
        scaleMetric(timeframe) {
            let scale = parseInt(this.amount);
            let pct = scale / 100;
            let metric = this.adjustMetric;
            let init = this.adjustInitiative;
            if (timeframe == 'month') {
                let month = this.month;
                this.initiativeData[init][metric][month] = this.initiativeData[init][metric][month] + Math.round(this.initiativeData[init][metric][month] * pct);
            } else if (timeframe == 'all') {
                _.forEach(this.forecastMonths, month => {
                    this.initiativeData[init][metric][month] = this.initiativeData[init][metric][month] + Math.round(this.initiativeData[init][metric][month] * pct);
                })
            }
        },
        distMetric(timeframe) {
            let dist = parseInt(this.amount);
            let metric = this.adjustMetric;
            let init = this.adjustInitiative;
            if (timeframe == 'month') {
                let month = this.month;
                this.initiativeData[init][metric][month] = dist;
            } else if (timeframe == 'all') {
                let currentSum = 0
                _.forEach(this.forecastMonths, month => {
                    currentSum += this.initiativeData[init][metric][month];
                });
                if (currentSum > 0) {
                    _.forEach(this.forecastMonths, month => {
                        let giveMonth = (this.initiativeData[init][metric][month] / currentSum) * dist
                        this.initiativeData[init][metric][month] = parseInt(giveMonth);
                    });
                } else {
                    _.forEach(this.forecastMonths, month => {
                        let giveMonth = dist / this.forecastMonths.length;
                        this.initiativeData[init][metric][month] = parseInt(giveMonth);
                    });
                }
                
            }
        },
    },
}
</script>

<style scoped>

table {
    table-layout: fixed;
    width: 100%;
}
.forecast-input {
    display: block; 
    padding: 0; 
    margin: 0; 
    border: 0; 
    width: 100%; 
    background: transparent;
    text-align: right;
    font-size: 1.1rem;
    color: #696969;
    outline: 0;
}
</style>