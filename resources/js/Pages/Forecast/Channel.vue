<template>
    <div class="uk-container">

        <ChannelTotals 
            :totals="totals"
            :budgetTotals="budgetTotals"
            :year="year"
            :month="month"
        />

        <!-- Card for each initiative -->
        <div v-for="(init, i) in initiativeList" :key="i" class="mt-6 bg-white rounded shadow">
            <div class="flex justify-between px-6 py-4 border-b border-gray-200">
                <div class="card-header-text" :class="{'blur' : loading }">
                    <span @click="showModal(init)">{{ init }}</span>

                    <!-- Adjust leads and Spend -->
                    <span class="text-sm ml-10 text-gray-400 hover:text-gray-600 duration-150">
                        <span @click="adjust(init, 'leads')" class="clickable" :class="(adjustMetric == 'leads' && adjustInitiative == init) ? 'uk-text-primary' : 'uk-text-muted'" data-balloon-pos="left" aria-label="Adjust Lead Inputs">Leads</span> 
                        <span class="uk-margin-small-left uk-margin-small-right uk-text-muted">|</span>
                        <span @click="adjust(init, 'spend')" class="clickable" :class="(adjustMetric == 'spend' && adjustInitiative == init) ? 'uk-text-primary' : 'uk-text-muted'" data-balloon-pos="right" aria-label="Adjust Spend Inputs">Spend</span>
                    </span>
                </div>


                <!-- Initiative Totals -->
                <span class="flex " :class="{'blur': loading, 'uk-text-muted': ! loading}">
                    <div class="flex" data-balloon-pos="left" :aria-label="`Budget: ${initTotal('leadsBudget', init).toLocaleString()}\nVariance: ${initVarianceString('leads', init)}`" data-balloon-break>
                        <svg class="text-gray-400 user-group w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span class="ml-3">{{ initTotal('leads', init) | commas }}</span>
                    </div>
                    <!-- <vk-drop :delay-hide="100">
                        <div class="uk-text-left">
                            <div>
                                <div class="uk-width-expand">
                                    Budget: <br>
                                    Variance:<br>
                                </div>
                                <div class="uk-text-right">
                                    {{ initTotal('leadsBudget', init) | commas }}<br>
                                    {{ initVariance('leads', init) | commas }}<br>
                                    <span :class="initPercentClass('leads', init)">
                                        <span v-if="initPercentVariance('leads', init) > 0">+</span>
                                        {{ initPercentVariance('leads', init) | pct0 }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </vk-drop> -->
                    <span class="mx-6 text-gray-200">|</span>
                    <!-- Spend -->
                    <span class="flex" data-balloon-pos="left" :aria-label="`Budget: $${initTotal('spendBudget', init).toLocaleString()}\nVariance: ${initVarianceString('spend', init)}`" data-balloon-break>
                        <svg class="text-gray-400 currency-dollar w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="ml-3">${{ initTotal('spend', init) | commas }}</span>
                    </span>
                    <!-- <vk-drop :delay-hide="100">
                        <div class="uk-text-left">
                            <div>
                                <div class="uk-width-expand">
                                    Budget: <br>
                                    Variance:<br>
                                </div>
                                <div class="uk-text-right">
                                    ${{ initTotal('spendBudget', init) | commas }}<br>
                                    <span v-if="initVariance('spend', init) < 0">
                                        -${{ Math.abs(initVariance('spend', init)) | commas }}
                                    </span>
                                    <span v-else>
                                        ${{ initVariance('spend', init) | commas }}
                                    </span>
                                    <br>
                                    <span :class="initPercentClass('spend', init)">
                                        <span v-if="initPercentVariance('spend', init) > 0">+</span>
                                        {{ initPercentVariance('spend', init) | pct0 }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </vk-drop> -->
                    <span class="mx-6 text-gray-200">|</span>
                    <!-- CPL -->
                    <span class="flex" data-balloon-pos="left" :aria-label="`Budget: $${initCplBudget(init).toLocaleString()}\nVariance: ${initVarianceString('cpl', init)}`" data-balloon-break>
                        <svg class="text-gray-400 scale w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        <span class="ml-3">${{ initCpl(init) | commas }}</span>
                    </span>
                    <!-- <vk-drop :delay-hide="100">
                        <div class="uk-text-left">
                            <div>
                                <div class="uk-width-expand">
                                    Budget: <br>
                                    Variance:<br>
                                </div>
                                <div class="uk-text-right">
                                    ${{ initCplBudget(init) | commas }}<br>
                                    <span v-if="initVariance('cpl', init) < 0">
                                        -${{ Math.abs(initVariance('cpl', init)) | commas }}
                                    </span>
                                    <span v-else>
                                        ${{ initVariance('cpl', init) | commas }}
                                    </span><br>
                                    <span :class="initPercentClass('cpl', init)">
                                        <span v-if="initPercentVariance('cpl', init) > 0">+</span>
                                        {{ initPercentVariance('cpl', init) | pct0 }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </vk-drop> -->
                </span>
            </div>

            <!-- Adjustment Inputs -->
            <div v-if="adjustMetric && adjustInitiative == init" class="mt-6">
                <div divided class="grid grid-cols-3 gap-4 px-6 py-4">
                    <div>
                        <div class="flex">
                            <label>Add {{ adjustMetric }} </label>
                            <span class="ml-4 text-gray-400 hover:text-gray-600 duration-150" data-balloon-pos="right" data-balloon-length="large"  :aria-label="`Input the ${adjustMetric } you to add to the current ${init} forecast, either for just the next month or all remaining months. \n \nA negative number subtracts ${adjustMetric }`" data-balloon-break>
                                <svg class="question-circle w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                        </div>
                        <div class="border border-gray-300 rounded px-2 py-1">
                            <input class="" type="number" v-model="add">
                        </div>
                        <div class="flex">
                            <div><button size="small" class="w-full" @click="addMetric('month')">{{ month }}</button></div>
                            <div><button size="small" class="w-full" @click="addMetric('all')">All</button></div>
                        </div>

                    </div>
                    <div>
                        <div class="flex">
                            Scale {{ adjustMetric }} (%)
                            <span class="ml-4" data-balloon-pos="right" data-balloon-length="large"  :aria-label="`Input the percent you want to adjust ${init} ${adjustMetric}, either for just the next month or all remaining months. \n \nA negative number reduces ${adjustMetric }`" data-balloon-break>
                                <svg class="question-circle w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                        </div>


                        <input class="" type="number" v-model="scale">
                        <div class="flex">
                            <div><button size="small" class="w-full" @click="scaleMetric('month')">{{ month }}</button></div>
                            <div><button size="small" class="w-full" @click="scaleMetric('all')">All</button></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex">
                            Distribute {{ adjustMetric }}
                            <span class="ml-4" data-balloon-pos="right" data-balloon-length="large"  :aria-label="`Input the total ${adjustMetric} you want to distribute for ${init}, either for just the next month or all remaining months.`" data-balloon-break>
                                <svg class="question-circle w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                        </div>


                        <input class="" type="number" v-model="dist">
                        <div class="flex">
                            <div><button size="small" class="w-full" @click="distMetric('month')">{{ month }}</button></div>
                            <div><button size="small" class="w-full" @click="distMetric('all')">All</button></div>
                        </div>
                    </div>
                </div>
                <hr>
            </div>
            <div class="px-6 py-4">
                <table class="table-fixed">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Metric</th>
                            <th v-for="(month, i) in months" :key="i" class="capitalize text-center px-4 py-2" @click="setActive('', '')">
                                {{ month.substring(0, 3) }}
                            </th>
                        </tr>
                    </thead>
                    <tbody :class="{'blur': loading}">
                        <tr class="bg-gray-50 hover:bg-yellow-50">
                            <td class="border px-4 py-2">Leads</td>
                            <td v-for="(month, i) in months" :key="i" class="text-right border px-4 py-2">
                                <span v-if="forecastMonths.includes(month)">
                                    <input v-model="initiativeData[init]['leads'][month]" type="number" min="0" v-show="! loading">
                                </span>
                                <span v-else class="past-values" :class="{'uk-text-muted' : ! loading }">
                                    {{ initiativeData[init]['leads'][month] }}
                                </span>
                            </td>
                        </tr>

                        <tr class=" hover:bg-yellow-50">
                            <td class="border px-4 py-2">Spend</td>
                            <td v-for="(month, i) in months" :key="i" class="text-right border px-4 py-2">
                                <span v-if="forecastMonths.includes(month)">
                                    <input v-model="initiativeData[init]['spend'][month]" type="number" min="0" v-show="! loading">
                                </span>
                                <span v-else class="past-values" :class="{'uk-text-muted' : ! loading }">
                                    {{ initiativeData[init]['spend'][month] }}
                                </span>
                            </td>
                        </tr>

                        <tr class="bg-gray-50 hover:bg-yellow-50">
                            <td class="border px-4 py-2">CPL</td>
                            <td v-for="(month, i) in months" :key="i" class="text-right border px-4 py-2">
                                <span v-if="forecastMonths.includes(month)">
                                    <input :value="cpl(init, month)" @change=" event => cplChange(init, month, event)" type="number" min="0"    v-show="! loading">
                                </span>
                                <span v-else class="past-values" :class="{'uk-text-muted' : ! loading }">
                                    {{ cpl(init, month) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class=" mt-6">
            <div class="uk-width-expand">
                <div>
                    <div class="bg-white rounded p-6 flex shadow">
                        <div class="uk-width-expand">
                            <input type="text" class="uk-input new-initiative" placeholder="Add Initiative" v-model="newInitiative" @keyup.enter="addInit()" >
                        </div>
                        <div>
                            <button @click="addInit()" class="m-4 bg-blue-500 text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg></button>
                        </div>                         
                    </div>
                </div>
            </div>
            <div v-if="channel == 'SEO' || channel == 'SEO2'">
                <div>
                    <button @click="getSeoData()" data-balloon-pos="bottom" :aria-label="'Calculate ' + channel + ' Forecasts'">
                        <!-- <font-awesome-icon icon="magic" /> -->
                        magic
                    </button>
                </div>
            </div>
            <div class="flex">
                <div>
                    <button v-if="approved" type="default" class="uk-width-1-1">Cannot Edit Approved Forecast</button>
                    <button v-else-if="status == 'open'" type="primary" class="uk-width-1-1" @click="submitForecast()" data-balloon-pos="bottom" :aria-label="program + ' ' + channel + '<br>Spend: $' + totals.spend.toLocaleString() + '<br>Leads: ' + totals.leads.toLocaleString()">Submit Forecast</button>
                    <button v-if="status == 'closed'" type="danger" class="uk-width-1-1" >Forecast is closed</button>
                </div>
            </div>
        </div>
        <p>&nbsp;</p>

        <!-- <vk-modal size="container" :show.sync="showChartModal">
            <vk-modal-close @click="showChartModal = false"></vk-modal-close>
            <vk-modal-title>{{ program }} {{ chartInitiative }} </vk-modal-title>
            <initiative-chart 
                v-if="showChartModal"
                :initiative="chartInitiative" 
                :profitability="profitability"
                :forecast="forecast"
                :months="months"
            />
        </vk-modal> -->
        
    </div>
</template>

<script>
import InitiativeChart from './InitiativeChart';
import ChannelTotals from './ChannelTotals';
import DateMixins from '../../Mixins/Dates'

export default {
    name: 'ForecastProgramChannel',

    mixins: [
        DateMixins
    ],

    components: {
        InitiativeChart,
        ChannelTotals
    },

    props: {
        year: {
            type: Number,
            default: 0
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
        status: {
            type: String,
            default: 'closed'
        }
    },

    data() {
        return {
            ready: false,
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
            dist: '',
            seoData: {},
            showChartModal: false,
            chartInitiative: ''
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
                spend: 0,
                monthLeads: 0,
                monthSpend: 0
            };
            _.forEach(this.initiativeList, init => {
                totals.leads += (this.initTotal('leads', init)) ? this.initTotal('leads', init) : 0;
                totals.spend += (this.initTotal('spend', init)) ? this.initTotal('spend', init) : 0;

                totals.monthLeads += (this.initiativeData[init] && this.initiativeData[init]['leads'][this.month]) ? parseInt(this.initiativeData[init]['leads'][this.month]) : 0;

                totals.monthSpend += (this.initiativeData[init] && this.initiativeData[init]['spend'][this.month]) ? parseInt(this.initiativeData[init]['spend'][this.month]) : 0;
            });
            totals.cpl = (totals.leads > 0) ? totals.spend / totals.leads : 0;
            totals.monthCpl = (totals.monthLeads > 0) ? totals.monthSpend / totals.monthLeads : 0;
            return totals;
        },
        budgetTotals() {
            let totals = {
                leads: 0,
                spend: 0,
                monthLeads: 0,
                monthSpend: 0
            };
            _.forEach(this.initiativeList, init => {
                _.forEach(this.months, month => {
                    totals.leads += (this.positiveProfitability[init]) ? this.positiveProfitability[init]['leadsBudget'][month] : 0;
                    totals.spend += (this.positiveProfitability[init]) ? this.positiveProfitability[init]['spendBudget'][month] : 0;
                });
                totals.monthLeads += (this.positiveProfitability[init]) ? this.positiveProfitability[init]['leadsBudget'][this.month] : 0;
                totals.monthSpend += (this.positiveProfitability[init]) ? this.positiveProfitability[init]['spendBudget'][this.month] : 0;
            });
            totals.cpl = (totals.leads > 0) ? totals.spend / totals.leads : 0;
            totals.monthCpl = (totals.monthLeads > 0) ? totals.monthSpend / totals.monthLeads : 0;
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
                if (month == this.month) { past = true; }
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
        showModal(init) {
            this.chartInitiative = init;
            this.showChartModal = true;
        },
        submitForecast() {
            let inputs = this.initiativeData;
            axios
                .post('/forecast/' + this.program + '/' + this.channel, inputs)
                .then(({data}) => {
                    console.log(data);
                    this.addMessage(`Forecast Submitted:<br> Leads: ${data.leads.toLocaleString()} <br>Spend: $ ${data.spend.toLocaleString()}`);
                    this.getForecast();
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
        getSeoData() {
            axios
                .get('/data/seo/forecasting/program/' + this.program)
                .then(({data}) => {
                    this.seoData = data;
                    this.fillSeoData();
                })
        },
        fillSeoData() {
            let data = _.filter(this.seoData, ['channel', this.channel]);
            _.forEach(data, d => {
                if (this.initiativeData[d.initiative]) {
                    _.forEach(this.forecastMonths, month => {
                        this.initiativeData[d.initiative]['leads'][month] = parseInt(d[month]);
                    });
                }
            });
            this.addMessage(this.channel + ' Leads Updated');
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
                if (_.isEmpty(this.initiativeData)) {
                    let allData = {
                        init: obj
                    };
                    this.initiativeData = allData;
                    console.log('was empty, but now: ' + allData);
                } else {
                    Vue.set(this.initiativeData, init, obj);
                }
                this.added.push(init);
                this.newInitiative = '';
                console.log('Initiative Added: ' + init);
            }
        },
        initTotal(metric, init) {
            let sum = 0;
            if (metric.includes('Budget')) {
                _.forEach(this.months, month => {
                    if (this.profitability[init] && this.profitability[init][metric][month]) {
                        sum += parseInt(this.profitability[init][metric][month]);
                    }
                });
            } else {
                _.forEach(this.months, month => {
                    if (this.initiativeData[init] && this.initiativeData[init][metric][month]) {
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
        initCplBudget(init) {
            let spend = this.initTotal('spendBudget', init);
            let leads = this.initTotal('leadsBudget', init);
            let cpl = (leads > 0) ? Math.round(spend / leads) : 0;
            return cpl;
        },
        initVariance(metric, init) {
            let base;
            let budget;
            if (metric == 'cpl') {
                base = this.initCpl(init);
                budget = this.initCplBudget(init);
            } else {
                base = this.initTotal(metric, init);
                budget = this.initTotal(metric + 'Budget', init);
            }
            return parseInt(base - budget);
        },
        initVarianceString(metric, init) {
            let value = this.initVariance(metric, init)
            if (metric == 'leads') {
                return value.toLocaleString()
            }
            if (value > 0) {
                return '$' + value.toLocaleString()
            }
            return '-$' + Math.abs(value).toLocaleString()
        },
        initPercentVariance(metric, init) {
            let budget;
            if (metric == 'cpl') {
                budget = this.initCplBudget(init);
            } else {
                budget = this.initTotal(metric + 'Budget', init);
            }
            let delta = this.initVariance(metric, init);
            return (budget > 0) ? delta / budget : 0;
        },
        initPercentClass(metric, init) {
            let pct = this.initPercentVariance(metric, init);
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
            this.ready = true;
        },
        adjust(initiative, metric) {
            if (this.adjustMetric == metric && this.adjustInitiative == initiative) {
                this.adjustMetric = '';
            } else {
                this.adjustMetric = metric;
            }
            this.adjustInitiative = initiative;
        },

        addMetric(timeframe) {
            let add = parseInt(this.add);
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
            let scale = parseInt(this.scale);
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
            let dist = parseInt(this.dist);
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

    watch: {
        program() {
            this.refreshData();
            this.ready = false;
        },
        channel() {
            this.refreshData();
            this.ready = false;
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
    margin-top: -20px;
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
.adjust-icon {
    color: #e5e5e5;
    transition-duration: .3s;
}
.adjust-icon:hover {
    color: #999999;
}
</style>