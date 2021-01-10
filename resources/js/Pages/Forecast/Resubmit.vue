<template>
    <div class="mt-8 mb-12 p-2">      
        <!-- Totals for each initiative -->
        <table class="table-fixed w-full">
            <thead>
                <tr>
                    <th>Initiative</th>
                    <th class="text-center capitalize">{{ settings.month }} Leads</th>
                    <th class="text-center capitalize">{{ settings.month }} Spend</th>
                    <th class="text-center capitalize">{{ settings.month }} CPL</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(init, i) in initiativeList" :key="i" class="hover:bg-yellow-50" :class="(i%2 == 0) ? 'bg-gray-50' : ''">
                    <td class="border px-4 py-2">{{ init }}</td>
                    <td class="text-right border px-4 py-2">{{ initiativeData[init]['leads'][settings.month] | commas }}</td>
                    <td class="text-right border px-4 py-2">${{ initiativeData[init]['spend'][settings.month] | commas }}</td>
                    <td class="text-right border px-4 py-2">${{ cpl(init, settings.month) | commas }}</td>
                </tr>
            </tbody>
        </table>
        <table class="table-fixed mt-8">
            <thead>
                <tr>
                    <th>Initiative</th>
                    <th class="text-center">Total {{ settings.year }} Leads</th>
                    <th class="text-center">Total {{ settings.year }} Spend</th>
                    <th class="text-center">Total {{ settings.year }} CPL</th>
                </tr>
            </thead>
            <tbody class="uk-text-right">
                <tr v-for="(init, i) in initiativeList" :key="i" class="hover:bg-yellow-50" :class="(i%2 == 0) ? 'bg-gray-50' : ''">
                    <td class="border px-4 py-2">{{ init }}</td>
                    <td class="text-right border px-4 py-2">
                        <span>
                            <span data-balloon-break data-balloon-pos="left" :aria-label="`Budget: ${formatMetric(initTotal('leadsBudget', init), 'leads')} \nVariance: ${formatMetric(initVariance('leads', init), 'leads')}`">
                                {{ initTotal('leads', init) | commas }}
                            </span>
                        </span>
                    </td>
                    <td class="text-right border px-4 py-2">
                        <span data-balloon-break data-balloon-pos="left" :aria-label="`Budget: ${formatMetric(initTotal('spendBudget', init), 'spend')} \nVariance: ${formatMetric(initVariance('spend', init), 'spend')}`">
                            ${{ initTotal('spend', init) | commas }}
                        </span>
                    </td>
                    <td class="text-right border px-4 py-2">${{ initCpl(init) | commas }}</td>
                </tr>
            </tbody>
        </table>

        <button class="mt-8 px-4 py-2 bg-gray-800 text-white hover:bg-blue-500 duration-200 rounded focus:outline-none" @click="submitForecast()" data-balloon-break data-balloon-pos="right" :aria-label="program + ' ' + channel + '\nSpend: $' + totals.spend.toLocaleString() + '\nLeads: ' + totals.leads.toLocaleString()">Duplicate Current Forecast</button>
    </div>
</template>

<script>
import DateMixins from '../../Mixins/Dates'
import FormatMetric from '../../Mixins/FormatMetric'

export default {
    name: 'ResubmitForecast',

    mixins: [
        DateMixins,
        FormatMetric    
    ],

    props: {
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
            loadingForecast: true,
            loadingProfitability: true,
            settings: {},
            added: [],
            forecast: {},
            profitability: {},
            initiativeData: {},
            newInitiative: '',
            adjustMetric: '',
            adjustInitiative: '',
            add: '',
            scale: '',
            dist: ''
        }
    },

    computed: {
        positiveProfitability() {
            let profit = this.profitability;
            _.forEach(profit, init => {
                let leads = 0;
                let spend = 0;
                _.forEach(this.months, month => {
                    leads += (init.leads[month]) ? parseInt(init.leads[month]) : 0;
                    spend += (init.spend[month]) ? parseInt(init.spend[month]) : 0;
                });
                if (leads == 0 && spend == 0) {
                    delete profit[init.name];
                }
            });
            return profit;
        },
        totals() {
            let totals = {
                leads: 0,
                spend: 0
            };
            _.forEach(this.initiativesWithData, init => {
                totals.leads += (this.initTotal('leads', init)) ? this.initTotal('leads', init) : 0;
                totals.spend += (this.initTotal('spend', init)) ? this.initTotal('spend', init) : 0;
            });
            totals.cpl = (totals.leads > 0) ? totals.spend / totals.leads : 0;
            return totals;
        },
        hasForecast() {
            if (! _.isEmpty(this.forecast)) {
                return true;
            }
            return false;
        },
        updated() {
            let first = this.forecast[Object.keys(this.forecast)[0]];
            return (first) ? first.updated : '';
        },
        approved() {
            let first = this.forecast[Object.keys(this.forecast)[0]];
            return (first) ? first.approved : '';
        },
        loading() {
            if (! this.loadingForecast && ! this.loadingProfitability) {
                return false;
            }
            return true;
        },
        range() {
            return this.yearDigits(this.calendarYear);
        },
        forecastMonths() {
            let array = [];
            let past = false;
            _.forEach(this.months, month => {
                if (month == this.settings.month) { past = true; }
                if (past) { array.push(month); }
            });
            return array;
        },

        initiativesWithData() {
            let array = [];
            _.forEach(this.initiativeData, init => {
                if (this.hasData(init)) {
                    array.push(init.name);
                } else if (this.added && this.added.includes(init.name)) {
                    array.push(init.name)
                }
            });
            return array;
        },
        initiativeList() {
            return _.keys(this.initiativeData);
        },

        emptyInitiative() {
            const empty = {
                leads: {},
                spend: {},
                leadsBudget: {},
                spendBudget: {}
            };
            
            empty.leads.program = this.program;
            empty.leads.channel = this.channel;
            empty.leads.initiative = '';
            empty.leads.metric = 'leads';

            empty.spend.program = this.program;
            empty.spend.channel = this.channel;
            empty.spend.initiative = '';
            empty.spend.metric = 'spend';

            _.forEach(this.months, month => {
                empty.leads[month] = 0;
                empty.spend[month] = 0;
                empty.leadsBudget[month] = 0;
                empty.spendBudget[month] = 0;
            });
            return empty;
        },
    },

    methods: {
        getSettings() {
            axios.get('/data/forecast/settings').then(({data}) => this.settings = data);
        },
        submitForecast() {
            let inputs = this.initiativeData;
            axios
                .post('/forecast/' + this.program + '/' + this.channel, inputs)
                .then(({data}) => {
                    console.log(data);
                    // this.addMessage(`Forecast Submitted:<br> Leads: ${data.leads.toLocaleString()} <br>Spend: $ ${data.spend.toLocaleString()}`);
                    this.getForecast();
                    this.$emit('submitted');
                })
        },
        getProfitability() {
            this.loadingProfitability = true;
            let filter = {
                program: this.program,
                channel: [this.channel],
                metric: 'forecast',
                group: 'initiative'
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
            this.forecast = {};
            axios
                .get('/data/forecast/program-channel/' + this.program + '/' + this.channel)
                .then(({data}) => {
                    this.forecast = data;
                    this.loadingForecast = false;
                });
        },

        // Check existing actuals / forecast / budget 
        hasData(initiative) {
            let sum = 0;
            _.forEach(initiative.leads, leads => {
                sum += leads;
            });
            if (sum > 0) {
                return true;
            }
            _.forEach(initiative.leadsBudget, leadsBudget => {
                sum += leadsBudget;
            });
            if (sum > 0) {
                return true;
            }
            _.forEach(initiative.spend, spend => {
                sum += spend;
            });
            if (sum > 0) {
                return true;
            }
            _.forEach(initiative.spendBudget, spendBudget => {
                sum += spendBudget;
            });
            if (sum > 0) {
                return true;
            }
            return false;
        },


        cpl(init, month) {
            let leads = 0;
            let spend = 0;
            let cpl = 0;

            if (this.initiativeData[init]['leads'][month]) {
                leads = this.initiativeData[init]['leads'][month];
            }
            if (this.initiativeData[init]['spend'][month]) {
                spend = this.initiativeData[init]['spend'][month];
            }
            if (leads > 0) {
                cpl = Math.round(spend / leads);
            }
            return cpl;
        },
        cplChange(init, month, event) {
            let cpl = event.target.value;
            let spend = this.initiativeData[init]['spend'][month];
            let newLeads = spend / cpl;
            this.initiativeData[init]['leads'][month] = Math.round(newLeads);

            console.log(`update CPL to ${cpl} for ${init} in the month of ${month}`);
        },

        addInit(key = '') {
            let init = (key != '') ? key : this.newInitiative.toUpperCase();
            if (init && ! this.initiativeList.includes(init)) {
                // let init = this.newInitiative.toUpperCase();
                // Deep clone empty object
                const obj = JSON.parse(JSON.stringify(this.emptyInitiative));
                obj.name = init;
                obj.leads.initiative = init;
                obj.spend.initiative = init;
                // Reactive set
                Vue.set(this.initiativeData, init, obj);
                this.added.push(init);
                this.newInitiative = '';
                console.log('Initiative Added: ' + init);
            }
        },
        initTotal(metric, init) {
            let sum = 0;
            if (metric.includes('Budget')) {
                _.forEach(this.months, month => {
                    if (this.profitability[init] && this.profitability[init][metric] && this.profitability[init][metric][month]) {
                        sum += parseInt(this.profitability[init][metric][month]);
                    }
                });
            } else {
                _.forEach(this.months, month => {
                    if (this.initiativeData[init] && this.initiativeData[init][metric] && this.initiativeData[init][metric][month]) {
                        sum += parseInt(this.initiativeData[init][metric][month]);
                    }
                });
            }
            return sum;
        },
        initCpl(init) {
            let spend = this.initTotal('spend', init);
            let leads = this.initTotal('leads', init);
            let cpl = (leads > 0) ? spend / leads : 0;
            return cpl;
        },
        initVariance(metric, init) {
            let base = this.initTotal(metric, init);
            let budget = this.initTotal(metric + 'Budget', init);
            return parseInt(base - budget);
        },

        approve() {
            axios
                .post('/data/forecast/approve/' + this.program + '/' + this.channel)
                .then(({data}) => {
                    console.log(data);
                    this.addMessage('Forecast Approved');
                    this.getForecast();
                })
        },
        disapprove() {
            axios
                .post('/data/forecast/disapprove/' + this.program + '/' + this.channel)
                .then(({data}) => {
                    console.log(data);
                    this.addError('Approval Removed');
                    this.getForecast();
                })
        },
        refreshData() {
            this.getForecast();
            this.getProfitability();
        },
        adjust(initiative, metric) {
            this.adjustInitiative = initiative;
            if (this.adjustMetric == metric) {
                this.adjustMetric = '';
            } else {
                this.adjustMetric = metric;
            }
        },

        addMetric(timeframe) {
            let add = parseInt(this.add);
            let metric = this.adjustMetric;
            let init = this.adjustInitiative;
            if (timeframe == 'month') {
                let month = this.settings.month;
                this.initiativeData[init][metric][month] += add;
            } else if (timeframe == 'all') {
                _.forEach(this.forecastMonths, month => {
                    this.initiativeData[init][metric][month] += add;
                })
            }
        },
        scaleMetric(timeframe) {
            let scale = parseInt(this.scale);
            let pct = scale / 100;
            let metric = this.adjustMetric;
            let init = this.adjustInitiative;
            if (timeframe == 'month') {
                let month = this.settings.month;
                this.initiativeData[init][metric][month] = this.initiativeData[init][metric][month] + Math.round(this.initiativeData[init][metric][month] * pct);
            } else if (timeframe == 'all') {
                _.forEach(this.forecastMonths, month => {
                    this.initiativeData[init][metric][month] = this.initiativeData[init][metric][month] + Math.round(this.initiativeData[init][metric][month] * pct);
                })
            }
        },
        distMetric(timeframe) {
            let dist = parseInt(this.dist);
            let metric = this.adjustMetric;
            let init = this.adjustInitiative;
            if (timeframe == 'month') {
                let month = this.settings.month;
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

    watch: {
        program() {
            this.refreshData();
        },
        channel() {
            this.refreshData();
        },
        loading() {
            if (this.loading === false) {
                if (! _.isEmpty(this.forecast)) {
                    this.initiativeData = this.forecast;
                } else {
                    this.initiativeData = this.positiveProfitability;
                }
            }
        }
    },

    mounted() {
        this.getSettings();
        // this.resetUserFilter();
        this.refreshData();
    }
}
</script>

<style scoped>
table {
    table-layout: fixed;
    width: 100%;
}
.nexus-table {
    margin-top: 0px;
}
input {
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
.new-initiative, .adjust-input {
    border: #e5e5e5 1px solid;
    padding-left: 12px;
    text-align: left;
}
.card-header-icon {
    font-size: 1.1rem;
    color: rgb(209, 208, 208);
    padding-right: 6px;
}
.card-header-text {
    font-size: 1.2rem;
}
.past-values {
    margin-right: 10px;
}
.adjust-section {
    margin-top: -20px;
}
.uk-button-default {
    background-color: transparent;
    color: #1e87f0;
    border: 1px solid #1e87f0;
    transition-duration: .15s;
}
.uk-button-default:hover {
    background-color: #1e87f0;
    color: #fff;
    border: 1px solid transparent;
}
.divided {
    border-right: 4px solid #e5e5e5;
}
</style>