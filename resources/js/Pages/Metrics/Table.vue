<template>
    <div class="p-4">
        <!-- <TableTotals 
            :filter="filter" 
            :totals="allData"
            :list="tableItems"
            :comparison="comparison"
            :pace="pace"
        /> -->
        <TableTotalCards 
            :filter="filter" 
            :totals="allData"
            :list="tableItems"
            :comparison="comparison"
            :pace="pace"
        />

        <table class="table-fixed w-full">
            <thead>
                <tr>
                    <th 
                        v-for="(column, index) in tableColumns" 
                        :key="index" 
                        class=" text-gray-400 cursor-pointer uppercase w-1/2 px-4 py-2" 
                    >
                        <div class="flex text-sm">
                        <!-- Column title text -->
                            <span 
                                v-html="heading(column)" 
                                @click="setSort(column)" 
                                class="text-center flex-grow"
                                :class=" {'font-bold' : column == sort}"
                            />
                            <!-- Sort icon -->
                            <span v-if="column == sort" class="uk-float-right nexus-blue">
                                <span v-if="order == 'desc'">
                                    <svg class="arrow-down w-3 h-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4 4m0 0l-4-4m4 4V3"></path></svg>
                                </span>
                                <span v-else>
                                    <svg class="arrow-up w-3 h-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18"></path></svg>
                                </span>
                            </span>
                        </div>
                    </th>
                </tr>
            </thead>

            <!-- Data for selected table -->
            <tbody>
                <tr v-for="(item, j) in tableOrder" :key="j" class="hover:bg-yellow-50" :class="(j % 2 == 0) ? 'bg-gray-50' : ''">
                    <td v-for="(column, i) in tableColumns" :key="i" 
                        class="border px-4 py-2"
                        :class="{'text-right': i > 0}"
                    >
                        <!-- Metric -->
                        <span :class="cellClass(column, item)" >
                            <span v-if="column == 'name'" >
                                <span @click="setActive(item)" class="cursor-pointer">
                                    {{ item }}
                                </span>
                            </span>
                            <span v-else-if="column == 'leads'" @click="buildChart(column, item)" :aria-label="`Lead Budget: ${formatMetric(allData[item]['leadsBudget'], 'leads')}`" data-balloon-pos="left">
                                {{ formatMetric(allData[item][column], column) }} 
                            </span>
                            <span v-else-if="column == 'spend'" @click="buildChart(column, item)" :aria-label="`Spend Budget: ${formatMetric(allData[item]['spendBudget'], 'spend')}`" data-balloon-pos="left">
                                {{ formatMetric(allData[item][column], column) }} 
                            </span>
                            <span v-else-if="column == 'cpl'" @click="buildChart(column, item)" :aria-label="`CPL Budget: ${formatMetric(allData[item]['cplBudget'], 'cpl')}`" data-balloon-pos="left">
                                {{ formatMetric(allData[item][column], column) }} 
                            </span>
                            <span v-else @click="buildChart(column, item)">
                                {{ formatMetric(allData[item][column], column) }}
                            </span>
                        </span>
                        <!-- Metrics Drop -->
                        <!-- <vk-drop v-if="column != 'name' && cellDrop(column, item)" :delay-hide="100">
                            <vk-card class="uk-text-left">
                                <span v-html="cellDrop(column, item)"></span>
                            </vk-card>
                        </vk-drop> -->
                        <!-- Notes Drop -->
                        <!-- <span class="uk-float-right note-icon" v-if="tableNotes[item] && column == 'name'">
                            <font-awesome-icon icon="edit" />
                            <vk-drop position="right-center" :offset="10" class="notes-drop">
                                <vk-card class="uk-text-left">
                                    <div class="uk-panel uk-panel-scrollable uk-resize-vertical notes-scrollable">
                                        <span v-html="notesPopover(item)"></span>
                                    </div>
                                </vk-card>
                            </vk-drop>
                        </span> -->
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import FormatMetric from '../../Mixins/FormatMetric'
import TableTotals from './Totals'
import TableTotalCards from './TotalCards'

export default {
    name: 'MetricsTable',

    mixins: [
        FormatMetric
    ],

    components: {
        TableTotals,
        TableTotalCards
    },

    props: {
        filter: {
            type: Object,
            default: () => {}
        }
    },
    
    data() {
        return {
            range: [202001],
            pace: 1,
            tableData: {},
            sort: '',
            order: 'desc',
            notesLoading: false,
            tableColumns: [
                'name',
                'leads',
                'leadsBudgetPaceDelta',
                'spend',
                'spendBudgetDelta',
                'cpl',
                'cplBudgetDelta',
            ],
        }
    },

    computed: {
        
        comparison() {
            if (this.tableColumns.includes('leadsBudgetPaceDelta')) {
                return 'Budget';
            } else if (this.tableColumns.includes('leadsForecastPaceDelta') || this.tableColumns.includes('spendForecast')) {
                return 'Forecast';
            } else {
                return 'Budget';
            }
        },
        tableItems() {
            let data = this.tableData;
            let array = _.map(data, 'name');
            let compact = _.compact(array);
            if (this.selected && this.subgroup == 'channel') {
                console.log('selected with channel');
            }
            return compact;
        },
        tableOrder() {
            let array = [];
            let items = this.allData;
            _.forEach(items, item => array.push(item));
            let sorted = _.orderBy(array, [this.sort], this.order);
            let order = _.map(sorted, 'name')
            return order;
        },

        dataColumns() {
            let cols = [];
            if (this.comparison == 'Budget') {
                cols = [
                    'name',
                    'leads', 
                    'leadsBudget', 
                    'leadsBudgetPercent',
                    'leadsBudgetPace', 
                    'leadsBudgetPaceDelta', 
                    'leadsBudgetPacePercent', 
                    'spend', 
                    'spendBudget', 
                    'spendBudgetDelta', 
                    'spendBudgetPercent',
                    'cpl', 
                    'cplBudget', 
                    'cplBudgetDelta',
                    'cplBudgetPercent'
                ];
            } else if (this.comparison == 'Forecast') {
                cols = [
                    'name',
                    'leads', 
                    'leadsForecast', 
                    'leadsForecastPercent',
                    'leadsForecastPace', 
                    'leadsForecastPaceDelta', 
                    'leadsForecastPacePercent', 
                    'spend', 
                    'spendForecast', 
                    'spendForecastDelta', 
                    'spendForecastPercent', 
                    'cpl', 
                    'cplForecast', 
                    'cplForecastDelta',
                    'cplForecastPercent'
                ];
            }
            return cols
        },

        allData() {
            let items = this.tableItems
            let cols = this.dataColumns
            let forecast = this.forecast

            let data = {};
            _.forEach(items, item => {
                data[item] = {};
                _.forEach(cols, col => {
                    data[item][col] = 0;
                    switch (col) {
                        case 'name':
                            data[item][col] = item;
                            break;
                        case 'leads':
                            data[item][col] = (this.tableData && this.tableData[item] && this.tableData[item].leads) ? this.tableData[item].leads : 0;
                            break;
                        case 'leadsProjection':
                            data[item][col] = Math.round(data[item].leads / this.pace);
                            break;
                        case 'spend':
                            data[item][col] = (this.tableData[item] && this.tableData[item].spend) ? this.tableData[item].spend : 0;
                            break;
                        case 'cpl':
                            data[item][col] = (data[item].spend && data[item].leads > 0) ? data[item].spend / data[item].leads : 0;
                            break;
                        // LeadsBudget
                        case 'leadsBudget':
                            data[item][col] = (this.tableData[item] && this.tableData[item].leadsBudget) ? this.tableData[item].leadsBudget : 0;
                            break;
                        case 'leadsBudgetPace':
                            data[item][col] = data[item].leadsBudget * this.pace;
                            break;
                        case 'leadsBudgetPaceDelta':
                            data[item][col] = data[item].leads - data[item].leadsBudgetPace;
                            break;
                        // Leads & Spend Forecast
                        case 'leadsForecast':
                            let fcst = (forecast && forecast[item]) ? forecast[item].leads : [];
                            data[item][col] = 0;
                            _.forEach(this.rangeArray, mo => {
                                data[item][col] += fcst[mo];
                            })
                            break;
                        case 'leadsForecastPace':
                            data[item][col] = data[item].leadsForecast * this.pace;
                            break;
                        case 'leadsForecastPaceDelta':
                            data[item][col] = data[item].leads - data[item].leadsForecastPace;
                            break;
                        case 'spendForecast':
                            let fcstSpend = (forecast && forecast[item]) ? forecast[item].spend : [];
                            data[item][col] = 0;
                            _.forEach(this.rangeArray, mo => {
                                data[item][col] += fcstSpend[mo];
                            })
                            break;
                        case 'cplForecast':
                            data[item][col] = (data[item].leadsForecast > 0) ? data[item].spendForecast / data[item].leadsForecast : 0;
                            break;

                        // Spend & CPL
                        case 'spendBudget':
                            data[item][col] = (this.tableData[item] && this.tableData[item].spendBudget) ? this.tableData[item].spendBudget : 0;
                            break;
                        case 'cplBudget':
                            data[item][col] = (data[item].leadsBudget > 0) ? data[item].spendBudget / data[item].leadsBudget : 0;
                            break;
                        // Budget Variance
                        case 'spendBudgetDelta':
                            data[item][col] = (data[item].spend && data[item].spendBudget) ? data[item].spend - data[item].spendBudget : 0;
                            break;
                        case 'cplBudgetDelta':
                            data[item][col] = data[item].cpl - data[item].cplBudget;
                            break;
                        // Forecast Variance
                        case 'spendForecastDelta':
                            data[item][col] = data[item].spend - data[item].spendForecast;
                            break;
                        case 'cplForecastDelta':
                            data[item][col] = data[item].cpl - data[item].cplForecast;
                            break;
                        // Budget Percent Achieved
                        case 'leadsBudgetPercent':
                            data[item][col] = (data[item].leadsBudget > 0) ? data[item].leads / data[item].leadsBudget : 0;
                            break;
                        case 'leadsBudgetPacePercent':
                            data[item][col] = (data[item].leadsBudgetPace > 0) ? data[item].leads / data[item].leadsBudgetPace : 0;
                            break;
                        case 'spendBudgetPercent':
                            data[item][col] = (data[item] && data[item].spend && data[item].spendBudget > 0) ? data[item].spend / data[item].spendBudget : 0
                            break;
                        case 'spendForecastPercent':
                            data[item][col] = (data[item].spendForecast > 0) ? data[item].spend / data[item].spendForecast : 0
                            break;
                        case 'cplBudgetPercent':
                            data[item][col] = (data[item].cplBudget > 0) ? data[item].cpl / data[item].cplBudget : 0
                            break;
                        // Forecast Percent Achieved
                        case 'leadsForecastPercent':
                            data[item][col] = (data[item].leadsForecast > 0) ? data[item].leads / data[item].leadsForecast : 0;
                            break;
                        case 'leadsForecastPacePercent':
                            data[item][col] = (data[item].leadsForecastPace > 0) ? data[item].leads / data[item].leadsForecastPace : 0;
                            break;
                        case 'cplForecastPercent':
                            data[item][col] = (data[item].cplForecast > 0) ? data[item].cpl / data[item].cplForecast : 0
                            break;

                        default:
                            return col + ' ' + item;
                            break;
                    }
                });
            });
            return data;
        },
    },

    methods: {
        getData() {
            let range = this.range
            let filter = this.filter
            axios
                .get('/data/metrics/totals', {
                    params: {
                        filter,
                        range,
                    }
                })
                .then(({data}) => {
                    this.tableData = data.data
                });
        },
        setSort(metric) {
            if (this.sort == metric) {
                this.toggleOrder();
            } else {
                this.sort = metric;
                this.order = 'desc';
            }
        },
        toggleOrder() {
            if (this.order == 'desc') {
                this.order = 'asc';
            } else if (this.order == 'asc') {
                this.order = 'desc';
            }
        },

        setActive(item) {
            if (this.selected) {
                this.secondModal = true;
                this.secondSelected = item;
                return null;
            }
            if (this.group == 'code' || this.group == 'program') {
                if (this.channel && this.channel.length > 0) {
                    this.setSubgroup('initiative');
                } else {
                    this.setSubgroup('channel');
                }
            }
            if (this.group == 'location' || this.group == 'bu') {
                this.setSubgroup('code');
            }
            this.setSelectedItem(item);
        },

        heading(column) {
            let string = '';
            switch (column) {
                case 'leadsBudgetPaceDelta':
                    string = 'lead bdgt';
                    if (this.pace < 1) {
                        string += ' pace ';
                    }
                    string += ' &Delta;';
                    return string
                    break;
            
                case 'leadsForecastPaceDelta':
                    string = 'lead fcst';
                    if (this.pace < 1) {
                        string += ' pace ';
                    }
                    string += ' &Delta;';
                    return string
                    break;
            
                default:
                    return column.replace('Budget', ' Bdgt').replace('Forecast', ' Fcst').replace('Delta', ' &Delta;').replace('Pace', ' Pace').replace('Percent', ' %');
                    break;
            }
        },

        cellClass(column, item) {
            switch (column) {
                case 'leads':
                case 'spend':
                case 'cpl':
                    return 'clickable';
                    break;
                case 'leadsBudgetPaceDelta':
                case 'leadsForecastPaceDelta':
                    return this.leadsPercentClass(item);
                    break;
                case 'cplBudgetDelta':
                case 'cplForecastDelta':
                    return this.cplPercentClass(item);
                    break;
            
                default:
                    return '';
                    break;
            }
        },

        leadsPercentClass(item) {
            if (this.allData[item].leads == 0 || this.allData[item].leadsBudgetPace == 0 || this.notesLoading) {
                return '';
            }
            let percent = 0;
            if (this.comparison == 'Budget') {
                percent = this.allData[item].leadsBudgetPacePercent;
            } else if (this.comparison == 'Forecast') {
                percent = this.allData[item].leadsForecastPacePercent;
            }
            if (percent > 1.1) {
                return 'text-green-500';
            } else if (percent < 0.9) {
                return 'text-red-500';
            } else {
                return '';
            }
        },
        cplPercentClass(item) {
            if (this.allData[item].cpl == 0 || this.allData[item].cplBudget == 0 || this.notesLoading) {
                return '';
            }
            let percent = 0;
            if (this.comparison == 'Budget') {
                percent = this.allData[item].cpl / this.allData[item].cplBudget;
            } else if (this.comparison == 'Forecast') {
                percent = this.allData[item].cpl / this.allData[item].cplForecast;
            }
            if (percent > 1.1) {
                return 'text-red-500';
            } else if (percent < 0.9) {
                return 'text-green-500';
            } else {
                return '';
            }
        },
    },

    mounted() {
        this.getData()
    }
}
</script>

<style>

</style>