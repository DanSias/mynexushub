<template>
    <div class="p-4">
        <table class="table-auto">
            <thead>
                <tr>
                    <th v-for="(key, i) in visibleColumnKeys" :key="i" class="px-4 py-2">
                        <span v-html="columns[key].label"></span>
                    </th>
                </tr>
            </thead>
            <tbody class="border-t">
                <tr v-for="(deadline, j) in deadlines" :key="j" class="hover:bg-yellow-50">
                    <td v-for="(key, i) in visibleColumnKeys" :key="i" :class="(['program', 'semester', 'term'].includes(key)) ? 'text-left' : 'text-right'" class="border px-4 py-2">
                        <span v-if="key.includes('cvrs')" :class="cvrsClass(deadline, key)">
                            <span class="cursor-pointer" @click="activateModal('cvrs', deadline)">
                                {{ cellValue (deadline, key) }}
                            </span>
                        </span>
                        <span v-else-if="key.includes('Rate')">
                            {{ cellValue (deadline, key) | pct1 }}
                        </span>
                        <span v-else-if="key.includes('starts')">
                            <span class="cursor-pointer" @click="activateModal('starts', deadline)">
                                {{ cellValue (deadline, key) | commas }}
                            </span>
                        </span>
                        <span v-else-if="key.includes('leads')" :class="baseClass(deadline, key)">
                            <span class="cursor-pointer" @click="activateModal('leads', deadline)">
                                {{ cellValue (deadline, key) | commas }}
                            </span>
                        </span>
                        <span v-else :class="baseClass(deadline, key)">
                            <span v-if="key.includes('revenue') || key == 'spend' || key == 'cpl'">$</span>
                            {{ cellValue (deadline, key) | commas }}
                            <span v-if="key.includes('revenue')">k</span>
                        </span>
                        <!-- <vk-drop v-if="key == 'leads'" :delay-hide="100">
                            <vk-card class="uk-text-left">
                                {{ yearMonthToText(deadline.open) }} - {{ yearMonthToText(deadline.close) }}
                                <hr class="uk-margin-small-top uk-margin-small-bottom">
                                <vk-grid>
                                    <div>
                                        Lead Budget: <br>
                                        <strong>{{ cellValue (deadline, 'leadsBudget') | commas }} </strong><br>
                                    </div>
                                    <div>
                                        <span :class="percentClass(cellValue(deadline, 'leadsBudgetAchieved'))">
                                            Achieved: <br>{{ cellValue (deadline, 'leadsBudgetAchieved') | pct0 }}
                                        </span>
                                    </div>
                                </vk-grid>
                            </vk-card>
                        </vk-drop>
                        <vk-drop v-if="key == 'term'" :delay-hide="100">
                            <vk-card>
                                {{ deadline.semester }} {{ deadline.term }} {{ deadline.year }} App Deadline: <br>
                                <strong>{{ deadline.text }} </strong><br>
                                <span class="uk-text-muted">({{ deadline.when }})</span>
                            </vk-card>
                        </vk-drop>
                        <vk-drop v-if="key == 'program'" :delay-hide="100">
                            <vk-card>
                                <strong>{{ deadline.program }}</strong><br>
                                Spend Strategy: <span  :class="strategyClass(programStrategy[deadline.program])">{{ programStrategy[deadline.program] }}</span><br>
                                Lifetime Value: ${{ programValues[deadline.program] | commas }}
                            </vk-card>
                        </vk-drop> -->
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Refresh Button -->
        <portal to="overview-data-refresh">
            <vk-icon-link 
                reset 
                @click.prevent="refreshData()" 
                icon="refresh" 
                class="uk-margin-small-right close-icon"
                v-vk-tooltip="'Refresh Data'"
            ></vk-icon-link>
        </portal>

        <!-- Leads Modal -->
        <!-- <vk-modal size="container" :show.sync="showModal">
            <vk-modal-close @click="showModal = false"></vk-modal-close>
            <vk-modal-title slot="header">
                {{ modalProgram }} - {{ modalSemester }} {{ modalTerm }}
            </vk-modal-title>
            <leads-chart 
                :labels="modalRange" 
                :actuals="modalArray" 
                :budget="modalBudgetArray"
            />
        </vk-modal> -->

        <!-- Revenue Modal -->
        <!-- <vk-modal size="container" :show.sync="showRevenueModal">
            <vk-modal-close @click="showRevenueModal = false"></vk-modal-close>
            <vk-modal-title slot="header" class="uk-flex">
                <span class="uk-margin-xlarge-right">{{ modalProgram }}</span> <span class="uk-text-muted"><portal-target name="overview-metric-switch"></portal-target></span>
            </vk-modal-title>
            <revenue-chart 
                v-if="showRevenueModal"
                :program="modalProgram"
            />
        </vk-modal> -->

        <!-- Conversion Modal -->
        <!-- <vk-modal size="container" :show.sync="showConversionModal">
            <vk-modal-close @click="showConversionModal = false"></vk-modal-close>
            <vk-modal-title slot="header" class="uk-flex">
                <span class="uk-margin-xlarge-right">{{ modalProgram }}</span>
            </vk-modal-title>
            <conversion-chart 
                v-if="showConversionModal"
                :program="modalProgram"
            />
        </vk-modal> -->

    </div>
</template>

<script>
// import LeadsChart from './Chart';
// import RevenueChart from './RevenueChart';
// import ConversionChart from './ConversionChart';
import DateMixins from '../../Mixins/Dates'

export default {
    name: 'OverviewTable',

    components: {
        // LeadsChart,
        // RevenueChart,
        // ConversionChart,
    },

    props: [
        'ready',
        // 'deadlines',
        'columns',
        'filter'
    ],

    mixins: [DateMixins],

    data() {
        return {
            deadlines: {},
            leads: {},
            pipeline: {},
            conversion: {},
            revenue: {},
            modalProgram: '',
            modalTerm: '',
            modalSemester: '',
            modalRange: [],
            modalArray: [],
            modalBudgetArray: [],
            showModal: false,
            showRevenueModal: false,
            showConversionModal: false,
        }
    },

    computed: {
        semester() {
            return (this.filter.semester) ? this.filter.semester : ''
        },
        filterString() {
            let string = ''
            _.forEach(this.filter, (value, key) => {
                string += (value) ? key + value.toString() : ''
            })
            return string
        },
        visibleColumnKeys() {
            let array = [];
            _.forEach(_.keys(this.columns), column => {
                if (this.columns[column].visible) {
                    array.push(column);
                }
            });
            return array;
        },
    },

    methods: {
        setSemester(semester) {
            this.$emit('setSemester', semester)
        },
        getNextDeadline() {
            axios
                .get('/deadlines/next', {
                    params: {
                        filter: this.filter
                    }
                })
                .then(({data}) => {
                    this.setSemester(data.semester);
                })
        },
        getDeadlines() {
            axios
                .get('/deadlines', {
                    params: {
                        filter: this.filter
                    }
                })
                .then(({data}) => {
                    this.deadlines = data.deadlines
                });
        },
        getLeads () {
            axios
                .get('/data/metrics/overview', {
                    params: {
                        filter: this.filter
                    }
                })
                .then(({data}) => {
                    this.leads = data
                })
        },

        getPipeline () {
            axios
                .get('/data/metrics/overview/pipeline', {
                    params: {
                        filter: this.filter
                    }
                })
                .then(({data}) => {
                    this.pipeline = data.pipeline
                })
        },

        getConversionRates () {
            axios
                .get('/data/term-conversion', {
                    params: {
                        filter: this.filter
                    }
                })
                .then(({data}) => {
                    this.conversion = data
                });
        },

        getRevenue (context) {
            axios
                .get('/data/revenue/program-semester', {
                    params: {
                        filter: this.filter
                    }
                })
                .then(({data}) => {
                    this.revenue = data
                });
        },
        incomplete(yearMonth) {
            if (parseInt(yearMonth) >= parseInt(this.calendarYearMonth)) {
                return true;
            } 
            return false;
        },
        cellValue(deadline, column) {
            let term = deadline.term;

            switch (column) {
                case 'program':
                    return deadline.program;
                    break;
                case 'semester':
                    return deadline.semester;
                    break;
                case 'term':
                    return deadline.term;
                    break;

                case 'leads':
                case 'spend':
                case 'leadsBudget':
                    let sum = 0;
                    let yearMonthArray = this.buildRangeArray(deadline.open, deadline.close);
                    _.forEach(yearMonthArray, yearMonth => {
                        sum += (this.leads[deadline.program]) ? this.leads[deadline.program][column][yearMonth] : 0;
                    });
                    return sum;
                    break;
                case 'leadsBudgetAchieved':
                    let leads = this.cellValue(deadline, 'leads');
                    let leadsBudget = this.cellValue(deadline, 'leadsBudget');
                    return (leadsBudget > 0) ? leads / leadsBudget : 0;
                    break;

                case 'cpl':
                    let leadSum = this.cellValue(deadline, 'leads');
                    let spendSum = this.cellValue(deadline, 'spend');
                    return (leadSum > 0) ? spendSum / leadSum : 0;
                    break;

                case 'acc':
                case 'acc90':
                case 'accconf':
                case 'accconf120':
                case 'aip':
                case 'app30':
                case 'comfile':
                case 'comfile60':
                case 'contact':
                case 'contact15':
                case 'insales':
                case 'quality':
                case 'quality30':
                case 'startsleadmonth':
                case 'startsleadmonth90':
                case 'startsleadmonth180':
                    let pipelineSum = 0;
                    let monthArray = this.buildRangeArray(deadline.open, deadline.close);
                    _.forEach(monthArray, month => {
                        pipelineSum += (this.pipeline[deadline.program]) ? this.pipeline[deadline.program][column][month] : 0;
                    });
                    return pipelineSum;
                    break;
                
                case 'insalesAppRate':
                    let insales = this.cellValue(deadline, 'insales');
                    let app = this.cellValue(deadline, 'aip');
                    return (insales > 0) ? app / insales : 0;
                    break;

                case 'cvrsTarget':
                    let tc = (this.conversion[deadline.program]) ? this.conversion[deadline.program][term] : {};
                    return (tc && tc.plan_leads > 0) ? ((tc.plan_starts / tc.plan_leads) * 100).toFixed(2) + '%' : 0;
                    break;
                case 'cvrsForecast':
                    let termconv = (this.conversion[deadline.program]) ? this.conversion[deadline.program][term] : {};
                    return (termconv && termconv.leads > 0) ? ((termconv.starts / termconv.leads) * 100).toFixed(2) + '%' : 0;
                    break;
                
                case 'cvrsVariance':
                    let tcon = (this.conversion[deadline.program]) ? this.conversion[deadline.program][term] : {};
                    let act = (tcon && tcon.leads > 0) ? ((tcon.starts / tcon.leads) * 100).toFixed(2) : 0;
                    let fct = (tcon && tcon.plan_leads > 0) ? ((tcon.plan_starts / tcon.plan_leads) * 100).toFixed(2) : 0;
                    return (fct > 0) ? Math.round(((act - fct) / fct) * 100) + '%' : 0;
                    break;

                case 'startsForecast':
                case 'studentsForecast':
                case 'revenueForecast':
                    let show = column.replace('Forecast', '');
                    let rev = (this.revenue[deadline.program]) ? this.revenue[deadline.program][term] : {};
                    // let rev = _.find(this.revenue, ['term', term]);
                    return (rev) ? rev[show] : 0;
                    break;
                case 'startsTarget':
                case 'studentsTarget':
                case 'revenueTarget':
                    let shown = 'plan_' + column.replace('Target', '');
                    let r = (this.revenue[deadline.program]) ? this.revenue[deadline.program][term] : {};
                    // let r = _.find(this.revenue, ['term', term]);
                    return (r) ? r[shown] : 0;
                    break;
                case 'startsVariance':
                case 'studentsVariance':
                case 'revenueVariance':
                    let showVar = column.replace('Variance', '');
                    let varianceValue = this.cellValue(deadline, showVar + 'Forecast') - this.cellValue(deadline, showVar + 'Target');
                    return (isNaN(varianceValue)) ? '' : varianceValue;
                    break;

                default:
                    if (column.includes('Rate')) {
                        let baseColumn = column.replace('Rate', '');
                        let leads = this.cellValue(deadline, 'leads');
                        let baseValue = this.cellValue(deadline, baseColumn);
                        return parseInt(baseValue) / parseInt(leads);
                        break;
                    }
                    return 'tbd';
                    break;
            }
        },
        percentClass(pct) {
            if (pct >= 1.1) {
                return 'uk-text-success';
            } else if (pct <= 0.9) {
                return 'uk-text-danger';
            } else {
                return 'uk-text-muted';
            }
        },
        baseClass(deadline, column) {
            if (column == 'leads') {
                let pct = this.cellValue (deadline, 'leadsBudgetAchieved');
                if (pct >= 1.1) {
                    return 'uk-text-success';
                } else if (pct <= 0.9) {
                    return 'uk-text-danger';
                }
            } else if (column == 'startsVariance') {
                let pct = this.cellValue (deadline, 'startsVariance') / this.cellValue (deadline, 'startsTarget');
                if (pct >= 0.1) {
                    return 'uk-text-success';
                } else if (pct <= -0.1) {
                    return 'uk-text-danger';
                }
            }
            return '';
        },
        cvrsClass(deadline, column) {
            if (column == 'cvrsVariance') {
                let value = this.cellValue (deadline, 'cvrsVariance');
                value = (value) ? value.replace('%', '') : 0;
                value = parseInt(value);
                if (value > 10) {
                    return 'uk-text-success'
                } else  if (value < -10) {
                    return 'uk-text-danger'
                }
            }
            return '';
        },

        activateModal(type, deadline) {
            if (type == 'cvrs') {
                this.modalProgram = deadline.program;
                this.modalSemester = deadline.semester; 
                this.modalTerm = deadline.term; 
                this.showConversionModal = true;
            } else if (type == 'starts') {
                this.modalProgram = deadline.program;
                this.modalSemester = deadline.semester; 
                this.modalTerm = deadline.term; 
                this.showRevenueModal = true;
            } else if (type == 'leads') {
                this.modalProgram = deadline.program;
                this.modalSemester = deadline.semester; 
                this.modalTerm = deadline.term; 
                this.modalRange = this.termRangeArray(deadline);
                let modalArray = [];
                let modalBudgetArray = [];
                _.forEach(this.modalRange, r => {
                    modalArray.push(this.leads[deadline.program]['leads'][r]);
                    modalBudgetArray.push(this.leads[deadline.program]['leadsBudget'][r]);
                })
                this.modalArray = modalArray;
                this.modalBudgetArray = modalBudgetArray;
                this.showModal = true;
            }
        },

        termRangeArray(deadline) {
            let start = String(deadline.open);
            let end = String(deadline.close);
            let array = this.buildRangeArray(start, end);
            return array;
        },

        refreshData() {
            this.getLeads();
            this.getPipeline();
            this.getConversionRates();
            this.getRevenue();
        }
    },

    watch: {
        filterString() {
            this.refreshData();
        },
        ready() {
            this.refreshData();
        },
        semester() {
            this.refreshData();
        },
        showRevenueModal() {
            if (! this.showRevenueModal) {
                this.modalProgram = '';
                this.modalTerm = '';
            }
        },
        showConversionModal() {
            if (! this.showConversionModal) {
                this.modalProgram = '';
                this.modalTerm = '';
            }
        }
    },

    mounted() {
        this.refreshData()
        this.getNextDeadline()
        this.getDeadlines()
        // this.filter = this.requestFilter
    }
}
</script>

<style scoped>
tr:nth-child(even) {
    background-color: #f9fafb;
}
</style>