<template>
    <div class="w-full p-6">
        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th>Program</th>
                    <th class="px-4 py-2 text-center">
                        <!-- <font-awesome-icon 
                            class="toggle-progress-icon"
                            :icon="(progressType == 'submitted') ? 'check' : 'check-double'" 
                            :class="(progressType == 'submitted') ? 'uk-text-primary' : 'uk-text-success'" 
                            :aria-label="(progressType == 'submitted') ? 'Submitted Forecasts' : 'Approved Forecasts'"
                            @click="progressType = (progressType == 'submitted') ? 'approved' : 'submitted'"
                        /> -->
                        <svg class="text-gray-300 w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </th>
                    <th class="px-4 py-2 text-center capitalize" v-for="(column, i) in dataColumns" :key="i">
                        <span v-html="heading(column)"></span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(program, i) in programs" :key="i" :class="(i%2 == 0) ? 'bg-gray-50' : ''" class="hover:bg-yellow-50">
                    <td class="program-column border px-4 py-2">
                        <span @click="goHere(program)" class="clickable">{{ program }}</span>
                    </td>
                    <!-- Progress Percent -->
                    <td class="uk-table-shrink text-right border px-4 py-2">
                        <span v-if="progressType == 'submitted'" data-balloon-pos="right" :aria-label="(progress.submitted[program]) ? progress.submitted[program].length + ' Channels Submitted' : ''">
                            {{ submittedProgress[program] | pct0 }}
                        </span>
                        <span v-else-if="progressType == 'approved'" data-balloon-pos="right" :aria-label="(progress.approved[program]) ? progress.approved[program].length + ' Channels Approved' : ''">
                            {{ approvedProgress[program] | pct0 }}
                        </span>
                    </td>

                    <td v-for="(column, j) in dataColumns" :key="j" class="border px-4 py-2 text-right" :class="{'n-a-cell' : cellData(column, program) == 'n/a', 'between-year-month' : column == 'monthCpl'}">
                        <span v-html="cellData(column, program)" :aria-label="dropContents(column, program)" data-balloon-pos="down" data-balloon-break></span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import FormatMetric from '../../Mixins/FormatMetric'
import DateMixins from '../../Mixins/Dates'

export default {
    name: 'ForecastVarianceTable',

    mixins: [
        FormatMetric,
        DateMixins
    ],

    props: {
        tableType: {
            type: String,
            default: ''
        },
        year: {
            type: Number,
            default: 0
        },
        month: {
            type: String,
            default: ''
        },
        programs: {
            type: Array,
            default: function () {
                return [];
            }
        },
        expected: {
            type: Object,
            default: function () {
                return {};
            }
        },
        progress: {
            type: Object,
            default: function () {
                return {};
            }
        }
    },

    data() {
        return {
            forecast: {},
            profitability: {},
            progressType: 'submitted',
            metrics: [
                'leads',
                'spend',
                'cpl'
            ],
            dataColumns: [
                'monthLeads',
                'monthSpend',
                'monthCpl',
                'yearLeads',
                'yearSpend',
                'yearCpl'
            ],
        }
    },

    computed: {
        range() {
            return this.yearDigits(this.calendarYear);
        },
        submittedProgress() {
            let progress = {};
            _.forEach(this.programs, p => {
                if (! _.isEmpty(this.progress.submitted[p]) && ! _.isEmpty(this.expected[p])) {
                    progress[p] = (this.expected[p].length > 0) ? this.progress.submitted[p].length / this.expected[p].length : 0;
                } else {
                    progress[p] = 0;
                }
            });
            return progress;
        },
        approvedProgress() {
            let progress = {};
            _.forEach(this.programs, p => {
                if (! _.isEmpty(this.progress.approved[p]) && ! _.isEmpty(this.expected[p])) {
                    progress[p] = this.progress.approved[p].length / this.expected[p].length;
                } else {
                    progress[p] = 0;
                }
            });
            return progress;
        },

        headingSuffix() {
            if (this.tableType == 'variance') {
                return '&Delta;';
            } else if (this.tableType == 'percent') {
                return '% &Delta;'
            } 
            return '';
        }
    },

    methods: {
        goHere(program) {
            const router = this.$router;
            router.push('/forecast/' + program);
        },

        refreshData() {
            this.getForecast();
            this.getProfitability();
            // this.ready = true;
        },

        getForecast() {
            // this.loadingForecast = true;
            this.forecast = {};
            let filter = {
                programs: this.programs
            }
            axios
                .get('/data/forecast/programs', {
                    params: {
                        filter
                    }
                })
                .then(({data}) => {
                    this.forecast = data;
                    // this.loadingForecast = false;
                });
        },
        getProfitability() {
            // this.loadingProfitability = true;
            let filter = {
                programs: this.programs,
                group: 'program',
                subgroup: 'channel',
                metric: 'forecast'
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
                    // this.loadingProfitability = false;
                });
        },
        heading(column) {
            let string = '';
            if (column.includes('month')) {
                string = this.month + ' ' + this.year + '<br>';
            } else if (column.includes('year')) {
                string = 'Full Year ' + this.year + '<br>';
            }
            string += ' ';
            string += column.replace('month', '').replace('year', '');
            string += ' Forecast ';
            string += this.headingSuffix;
            return string;
        },

        fullYear(object) {
            let sum = 0;
            _.forEach(this.months, month => {
                sum += (object[month]) ? object[month] : 0;
            });
            return sum;
        },

        cellData(column, program) {
            if (! this.forecast[program]) {
                return 'n/a';
            }
            let channels = _.keys(this.forecast[program]);
            
            let newValue = 0;
            let oldValue = 0;

            let type = this.tableType;
            let baseMetric = column.replace('month', '').replace('year', '').toLowerCase();
            if (column.includes('month')) {
                switch (baseMetric) {
                    case 'leads':
                    case 'spend':
                        _.forEach(channels, channel => {
                            newValue += (this.forecast[program] && this.forecast[program][channel]) 
                                ? this.forecast[program][channel][baseMetric][this.month] 
                                : 0;
                            oldValue += (this.profitability[program] && this.profitability[program]['channel'][channel]) 
                                ? this.profitability[program]['channel'][channel][baseMetric][this.month] 
                                : 0;
                        });
                        if (type == 'variance') {
                            let delta = newValue - oldValue;
                            return this.formatMetric(delta, baseMetric);
                        } else if (type == 'percent') {
                            let pct = (oldValue > 0) ? (newValue - oldValue) / oldValue : 0;
                            return this.formatMetric(pct, 'pct1');
                        }
                        return this.formatMetric(newValue, baseMetric);
                        break;
                    case 'cpl':
                        if (type == 'variance') {
                            return this.formatMetric(this.cplDelta(program).delta, 'cpl');
                        } else if (type == 'percent') {
                            return this.formatMetric(this.cplDelta(program).percent, 'pct1');
                        }
                        return this.formatMetric(this.cplDelta(program).new, 'cpl');
                        break;
                    default:
                        break;
                }
            } else if (column.includes('year')) {
                switch (baseMetric) {
                    case 'leads':
                    case 'spend':
                        _.forEach(channels, channel => {
                            newValue += (this.forecast[program] && this.forecast[program][channel]) 
                                ? this.fullYear(this.forecast[program][channel][baseMetric]) 
                                : 0;
                            oldValue += (this.profitability[program] && this.profitability[program]['channel'][channel]) 
                                ? this.fullYear(this.profitability[program]['channel'][channel][baseMetric]) 
                                : 0;
                        });
                        if (type == 'variance') {
                            let delta = newValue - oldValue;
                            return this.formatMetric(delta, baseMetric);
                        } else if (type == 'percent') {
                            let pct = (oldValue > 0) ? (newValue - oldValue) / oldValue : 0;
                            return this.formatMetric(pct, 'pct1');
                        }
                        return this.formatMetric(newValue, baseMetric);
                        break;
                    case 'cpl':
                        if (type == 'variance') {
                            return this.formatMetric(this.cplFullYearDelta(program).delta, 'cpl');
                        } else if (type == 'percent') {
                            return this.formatMetric(this.cplFullYearDelta(program).percent, 'pct1');
                        }
                        return this.formatMetric(this.cplFullYearDelta(program).new, 'cpl');
                        break;
                    default:
                        break;
                }
            }
        },
        dropContents(column, program) {
            let baseMetric = column.replace('month', '').replace('year', '').toLowerCase();
            let budgetMetric = baseMetric + 'Budget';
            let channels = _.keys(this.forecast[program]);
            
            let newValue = 0;
            let oldValue = 0;
            let budgetValue = 0;
            
            if (column == 'monthLeads' || column == 'monthSpend') {
                _.forEach(channels, channel => {
                    newValue += (this.forecast[program] && this.forecast[program][channel]) 
                        ? this.forecast[program][channel][baseMetric][this.month]
                        : 0;
                    oldValue += (this.profitability[program] && this.profitability[program]['channel'][channel]) 
                        ? this.profitability[program]['channel'][channel][baseMetric][this.month]
                        : 0;
                    budgetValue += (this.profitability[program] && this.profitability[program]['channel'][channel]) 
                        ? this.profitability[program]['channel'][channel][budgetMetric][this.month]
                        : 0;
                })
            } else if (column == 'yearLeads' || column == 'yearSpend') {
                _.forEach(channels, channel => {
                    newValue += (this.forecast[program] && this.forecast[program][channel]) 
                        ? this.fullYear(this.forecast[program][channel][baseMetric])
                        : 0;
                    oldValue += (this.profitability[program] && this.profitability[program]['channel'][channel]) 
                        ? this.fullYear(this.profitability[program]['channel'][channel][baseMetric])
                        : 0;
                    budgetValue += (this.profitability[program] && this.profitability[program]['channel'][channel]) 
                        ? this.fullYear(this.profitability[program]['channel'][channel][budgetMetric])
                        : 0;
                });
            } else if (column == 'monthCpl') {
                newValue = this.cplDelta(program).new;
                oldValue = this.cplDelta(program).current;
            } else if (column == 'yearCpl') {
                newValue = this.cplFullYearDelta(program).new;
                oldValue = this.cplFullYearDelta(program).current;
            }

            let string = '';
            string += 'New Forecast: ';
            string += this.formatMetric(newValue, baseMetric);
            string += '\n';
            string += 'Prior Forecast: ';
            string += this.formatMetric(oldValue, baseMetric);
            if (budgetValue) {
                string += '\n';
                string += 'Budget: ';
                string += this.formatMetric(budgetValue, baseMetric);
            }
            return string
        },
        cplDelta(program) {
            let leads = 0;
            let spend = 0;
            let currentLeads = 0;
            let currentSpend = 0;

            let channels = _.keys(this.forecast[program]);
            _.forEach(channels, channel => {
                leads += (this.forecast[program] && this.forecast[program][channel]) 
                    ? this.forecast[program][channel]['leads'][this.month]
                    : 0;
                spend += (this.forecast[program] && this.forecast[program][channel]) 
                    ? this.forecast[program][channel]['spend'][this.month]
                    : 0;
                currentLeads += (this.profitability[program] && this.profitability[program]['channel'][channel]) 
                    ? this.profitability[program]['channel'][channel]['leads'][this.month]
                    : 0;
                currentSpend += (this.profitability[program] && this.profitability[program]['channel'][channel]) 
                    ? this.profitability[program]['channel'][channel]['spend'][this.month]
                    : 0;
            }); 
            let cpl = (leads > 0) ? spend / leads : 0;
            let currentCpl = (currentLeads > 0) ? currentSpend / currentLeads : 0;
            let obj = {
                new: Math.round(cpl),
                current: Math.round(currentCpl),
                delta: cpl - currentCpl,
            }
            obj.percent = (obj.current > 0) ? obj.delta / obj.current : 0;
            return obj;
        },
        cplFullYearDelta(program) {
            let leads = 0;
            let spend = 0;
            let currentLeads = 0;
            let currentSpend = 0;

            let channels = _.keys(this.forecast[program]);
            _.forEach(channels, channel => {
                leads += (this.forecast[program] && this.forecast[program][channel]) 
                    ? this.fullYear(this.forecast[program][channel]['leads']) 
                    : 0;
                spend += (this.forecast[program] && this.forecast[program][channel]) 
                    ? this.fullYear(this.forecast[program][channel]['spend']) 
                    : 0;
                currentLeads += (this.profitability[program] && this.profitability[program]['channel'][channel]) 
                    ? this.fullYear(this.profitability[program]['channel'][channel]['leads']) 
                    : 0;
                currentSpend += (this.profitability[program] && this.profitability[program]['channel'][channel]) 
                    ? this.fullYear(this.profitability[program]['channel'][channel]['spend']) 
                    : 0;
            }); 
            let cpl = (leads > 0) ? spend / leads : 0;
            let currentCpl = (currentLeads > 0) ? currentSpend / currentLeads : 0;
            let obj = {
                new: Math.round(cpl),
                current: Math.round(currentCpl),
                delta: cpl - currentCpl,
            }
            obj.percent = (obj.current > 0) ? obj.delta / obj.current : 0;
            return obj;
        },
    },

    mounted() {
        this.refreshData();
    },
}
</script>

<style scoped>
.toggle-progress-icon {
    outline: 0;
}
.n-a-cell {
    color: #e5e5e5;
    transition-duration: .3s;
}
.n-a-cell:hover {
    color: #999;
}
.between-year-month {
    border-right: 3px solid #d8d8d8;
}
</style>