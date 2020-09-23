<template>
    <div>
        <div class="flex">
            <div class="uk-width-expand">
                <h3 class="capitalize">
                    <span v-if="chartFilter && chartFilter.programs && chartFilter.programs.length > 0">{{ chartFilter.programs.toString() }}</span>
                    <span v-else-if="chartFilter && chartFilter.location">{{ chartFilter.location.toString() }}</span> 
                    <span v-if="chartFilter && chartFilter.channel">{{ chartFilter.channel.toString() }}</span> 
                    <span v-if="chartFilter && chartFilter.selected">{{ chartFilter.selected }}</span> 
                    
                    <!-- {{ metric }} -->
                    <!-- <span v-for="(icon, i) in ['line', 'bar', 'area']" :key="i" class="uk-margin-left">
                        <span @click="type = icon" :class="(type == icon) ? 'text-blue-500' : 'text-gray-500'">icon</span>
                    </span> -->
                </h3>
            </div>
            <div v-show="forecastPercent" class="text-gray-500">
                <p>Forecasting {{ forecastPercent | pct0 }} of Budget</p>
            </div>
        </div>

        <highcharts 
            class="uk-margin-small-top"
            :options="chartOptions" 
        />

        <chart-table 
            :metric="metric"
            :shortMonthsArray="shortMonthsArray"
            :monthsArray="monthsArray"
            :actualsArray="actualsArray"
            :budgetArray="budgetArray"
            :varianceArray="varianceArray"
        />

    </div>
</template>

<script>
import { Chart } from 'highcharts-vue';
import ChartTable from './ChartTable';

import DateMixins from '../../Mixins/Dates'

export default {
    name: 'MetricsChart',

    mixins: [
        DateMixins
    ],

    components: {
        ChartTable,
        highcharts: Chart,
    },

    props: {
        metric: {
            type: String,
            default: 'Leads'
        },
        focus: {
            type: String,
            default: ''
        },
        filter: {
            type: Object,
            default: () => {}
        },
    },
    
    data() {
        return {
            type: 'line',
            chart: {}
        }
    },

    computed: {
        chartFilter() { 
            return this.chart.filter 
        },

        forecastPercent() {
            let act = this.arraySum(this.actualsArray);
            let bud = this.arraySum(this.budgetArray);
            return (bud > 0) ? act / bud : 0;
        },

        prefix() {
            if (this.metric.toLowerCase() == 'spend' || this.metric.toLowerCase() == 'cpl') {
                return '$'
            } else {
                return '';
            }
        },

        lastYear() {
            return parseInt(this.calendarYear) - 1;
        },
        monthsArray() {
            let array = [];
            for (let i = 0; i < 12; i++) {
                let mo = this.months[i];
                let uc = mo.charAt(0).toUpperCase() + mo.slice(1);
                array.push(uc);
            }
            return array;
        },
        shortMonthsArray() {
            let array = [];
            _.forEach(this.monthsArray, mo => {
                array.push(mo.slice(0, 3));
            });
            return array;
        },

        actualsArray() {
            let table = 'monthly_actuals_' + this.calendarYear;
            let actuals = (this.chart.array) ? this.chart.array[table] : [];
            let forecast = this.forecastArray;
            let complete = this.completeMonths;
            let completeCount = (complete) ? complete.length : 0

            if (actuals === undefined) {
                return [];
            }

            let array = [];
            for (let i = 0; i < 12; i++) {
                if (i < completeCount) {
                    array.push(actuals[i]);
                } else {
                    array.push(forecast[i]);
                } 
            }
            return array;
        },
        budgetArray() {
            let table = 'monthly_budget_' + this.calendarYear;
            let array = (this.chart.array) ? this.chart.array[table] : [];
            return array;
        },
        forecastArray() {
            let table = 'monthly_forecast_' + this.calendarYear;
            let array = (this.chart.array) ? this.chart.array[table] : [];
            return array;
        },
        varianceArray() {
            let array = [];

            if (this.actualsArray.length == 0) {
                return [];
            }

            for (let i = 0; i < 12; i++) {
                let diff = this.actualsArray[i] - this.budgetArray[i];
                array.push(diff);
            }
            return array;
        },

        lastYear() {
            return parseInt(this.calendarYear) - 1;
        },
        lastYearArray() {
            let table = 'monthly_actuals_' + this.lastYear;
            let array = (this.chart.array) ? this.chart.array[table] : [];
            return array;
        },
        previousYear() {
            return parseInt(this.calendarYear) - 2;
        },
        previousYearArray() {
            let table = 'monthly_actuals_' + this.previousYear;
            let array = (this.chart.array) ? this.chart.array[table] : [];
            return array;
        },

        

        chartOptions() {
            let options = {
                title: {
                    text: ''
                },
                xAxis: {
                    categories: this.monthsArray,
                },
                yAxis: {
                    title: {
                        text: this.chart.metric
                    }
                },
                tooltip: {
                    valueDecimals: 0,
                    valuePrefix: this.prefix,
                },
                series: [{
                    name: this.calendarYear + ' Actuals / Forecast',
                    color: '#2F8DEA',
                    type: this.type,
                    data: this.actualsArray
                }, {
                    name: this.calendarYear + ' Budget',
                    color: '#f44336',
                    type: this.type,
                    data: this.budgetArray
                }, {
                    name: 'Variance',
                    type: 'column',
                    data: this.varianceArray,
                    visible: false,
                    threshold: 0,
                    negativeColor: '#EF9A9A',
                    color: '#A5D6A7'
                }, {
                    name: this.lastYear + ' Actuals',
                    color: '#81d4fa',
                    visible: false,
                    type: this.type,
                    data: this.lastYearArray
                }, {
                    name: this.previousYear + ' Actuals',
                    color: '#81d4fa',
                    visible: false,
                    type: this.type,
                    data: this.previousYearArray
                }],
                credits: {
                    enabled: false
                }
            }
            return options;
        },
    },

    methods: {
        getChartData() {
            console.log('do the data');
            let filter = JSON.parse(JSON.stringify(this.filter));
            filter.metric = this.metric.toLowerCase();
            // Focus prop, set as group
            if (this.focus != '') {
                if (this.filter.group == 'code') {
                    filter.programs = [this.focus];
                } else {
                    filter[this.filter.group] = [this.focus];
                    console.log('group: ' + this.filter.group + ' focus: ' + this.focus);
                }
            }
            // if (this.focusMetric) {
            //     filter.metric = this.focusMetric;
            // }

            axios
                .get('/data/chart/year', {
                    params: {
                        filter,
                    }
                })
                .then(({data}) => this.chart = data);
        },
        arraySum(array) {
            let sum = 0;
            _.forEach(array, el => {
                sum += el;
            });
            return sum;
        }
    },

    watch: {
        metric() {
            this.getChartData();
        }
    },
    mounted() {
        this.getChartData();
    }
}
</script>

<style scoped>
table {
  table-layout: fixed;
}
</style>
