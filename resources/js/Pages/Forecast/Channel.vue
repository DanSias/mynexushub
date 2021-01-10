<template>
    <div class="uk-container">
        <!-- Approval / Submission Date -->
        <portal to="ChannelApprovalDate">
            <span v-if="approved" class="text-sm text-green-500 flex mt-1">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                Approved {{ approved }}
            </span>
            <span v-else-if="updated" class="text-sm text-blue-500 flex mt-1">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>                
                Submitted {{ updated }}
            </span>
        </portal>

        <ChannelTotals 
            :totals="totals"
            :forecastTotals="forecastTotals"
            :year="year"
            :month="month"
        />

        <ForecastInitiative
            v-for="(init, i) in initiativeList" :key="i"
            :month="month"
            :months="months"
            :forecastMonths="forecastMonths"
            :program="program"
            :channel="channel"
            :init="init"
            :profitability="profitability[init]"
            :initiativeData="initiativeData[init]"
            :collapseAll="collapseAll"
        />

        <!-- Add New Initiative -->
        <div class="flex justify-between mt-6">
            <div class="flex text-center bg-white rounded shadow px-8 w-1/3 py-6">
                <div class="flex flex-grow">
                    <InitiativeSelect 
                        class="w-full"
                        :channel="channel" 
                        :placeholde="'Add Initiative'"
                        v-model="newInitiative"
                    />
                    <div class="ml-4">
                        <button @click="addInit()" class="capitalize w-full bg-gray-800 text-white rounded px-4 py-2 hover:bg-blue-500  duration-300 focus:outline-none"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg></button>
                    </div>                         
                </div>
            </div>
            <div class="text-center bg-white rounded shadow px-8 py-6">
                <div class="flex">
                    <button  
                        class=" rounded px-4 py-2 duration-200 flex focus:outline-none" 
                        :class="(collapseAll) ? 'bg-gray-800 text-white' : 'bg-gray-50 hover:bg-gray-200 text-gray-800 border border-gray-800'"
                        @click="toggleCollapse()"  
                        data-balloon-pos="right"
                        aria-label="Toggle Initiative Tables"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </button>
                    <button @click="getSeoData()" class=" rounded px-4 py-2 duration-200 flex focus:outline-none" data-balloon-pos="right" :aria-label="'Calculate ' + channel + ' Forecasts'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </button>
                </div>
            </div>
            <!-- <div v-if="channel == 'SEO' || channel == 'SEO2'">
            </div> -->
            <div class="flex text-center bg-white rounded shadow px-8 w-1/3 py-6">
                <div class="w-full">
                    <button v-if="approved" type="default" class="w-full bg-gray-100 border rounded px-4 py-2 cursor-not-allowed">Cannot Edit Approved Forecast</button>
                    <button v-else-if="status == 'open'" type="primary" 
                        class="capitalize w-full bg-gray-800 text-white rounded px-4 py-2 hover:bg-blue-500  duration-300" 
                        @click="submitForecast()"  
                    >
                        Submit Forecast
                    </button>
                    <button v-if="status == 'closed'" type="danger" class="uk-width-1-1" >Forecast is closed</button>
                </div>
            </div>
        </div>
        <p>&nbsp;</p>
    </div>
</template>

<script>
import ChannelTotals from './ChannelTotals';
import DateMixins from '../../Mixins/Dates'
import FormatMetric from '../../Mixins/FormatMetric'
import ForecastInitiative from './Initiative'
import InitiativeSelect from '../../Components/Select/ChannelInitiatives'

export default {
    name: 'ForecastProgramChannel',

    mixins: [
        DateMixins,
        FormatMetric
    ],

    components: {
        ChannelTotals,
        InitiativeSelect,
        ForecastInitiative
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
            added: [],
            forecast: {},
            profitability: {},
            initiativeData: {},
            newInitiative: null,
            seoData: {},
            showChartModal: false,
            chartInitiative: '',
            collapseAll: false,
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
        forecastTotals() {
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

        initiativeList() {
            return _.keys(this.initiativeData);
        },

        emptyInitiative() {
            let keys = ['leads', 'spend', 'cpl', 'leadsBudget', 'spendBudget', 'cplBudget']
            const empty = {
                name: this.newInitiative
            }

            _.forEach(keys, key => {
                empty[key] = {}
                empty[key].program = this.program
                empty[key].channel = this.channel
                empty[key].metric = key

                _.forEach(this.months, month => {
                    empty[key][month] = 0;
                });
            })

            return empty;
        },
    },

    methods: {
        
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
                    if (this.initiativeData[init][metric][month]) {
                        sum += parseInt(this.initiativeData[init][metric][month]);
                    }
                });
            }
            return sum;
        },
        showTable(init) {
            if (this.collapseAll) {
                return false
            }
        },
        showModal(init) {
            this.chartInitiative = init;
            this.showChartModal = true;
        },
        toggleCollapse() {
            this.collapseAll = ! this.collapseAll
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

        addInit(key = '') {
            let init = (key != '') ? key : this.newInitiative.toUpperCase();
            if (init && ! this.initiativeList.includes(init)) {

                // Deep clone empty object
                const obj = JSON.parse(JSON.stringify(this.emptyInitiative))
                // obj.name = init

                // Reactive set
                if (_.isEmpty(this.initiativeData)) {
                    let allData = {
                        [init]: obj
                    };
                    this.initiativeData = allData
                    console.log('was empty, but now: ' + allData)
                } else {
                    console.log('set the thing now')
                    this.$set(this.initiativeData, init, obj)
                    console.table(obj)
                    this.initiativeData[init] = obj
                }

                this.added.push(init);
                this.newInitiative = null;
                console.log('Initiative Added: ' + init);
            }
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
                    this.initiativeData = JSON.parse(JSON.stringify(this.forecast))
                } else {
                    this.initiativeData = JSON.parse(JSON.stringify(this.positiveProfitability))
                }
            }
        }
    },

    mounted() {
        this.refreshData();
    }
}
</script>
