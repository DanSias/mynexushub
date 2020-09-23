<template>
    <div>
        <portal to="refresh-revenue-button">
            <vk-icon-link 
                reset 
                @click.prevent="getRevenueData()" 
                icon="refresh" 
                class="uk-margin-small-right close-icon"
                v-vk-tooltip="'Refresh Data'"
            ></vk-icon-link>
        </portal>

        <!-- Revenue / Starts / Students Table -->
        <table class="uk-table uk-table-hover uk-table-striped uk-table-small nexus-table uk-margin-remove-top">
            <thead class="nexus-thead">
                <tr>
                    <th v-for="(column, i) in columns" :key="i" :class="columnClass(column)" @click="setSort(column)">
                        {{ columnHeading(column) }}
                        <span v-if="column == sort" class="uk-float-right">
                            <font-awesome-icon v-if="order == 'desc'" icon="caret-down" class="uk-text-primary" />
                            <font-awesome-icon v-else-if="order == 'asc'" icon="caret-up" class="uk-text-primary" />
                        </span>
                    </th>
                </tr>
            </thead>

            <tbody :class="{'blur': revenueLoading}">
                <tr v-for="(pro, index) in orderedList" :key="index">
                    <td v-for="(col, i) in columns" 
                        :key="i"
                        :class="{ 'uk-text-right': col != 'name' }"
                    >
                        <span v-if="col == 'name'">
                            <span class="clickable" @click="showRevenueModal(pro)">{{ pro }}</span>
                        </span>
                        <span v-else-if="col == 'achieved'">
                            {{ revenueCellValue(pro, col) | pct0 }}
                        </span>
                        <span v-else>
                            {{ formatMetric(revenueCellValue(pro, col), revenueTable.metric.toLowerCase()) }}
                        </span>
                        <vk-drop v-if="col != 'name'">
                            <vk-card class="uk-text-left">
                                <span v-html="tooltipContents(pro, col)"></span>
                            </vk-card>
                        </vk-drop>
                    </td>
                </tr>
            </tbody>
        </table>

        <portal to="revenue-totals-row">
            <vk-grid divided class="uk-child-width-expand">
                <div class="uk-text-center">
                    {{ metric | ucFirst }} Actuals
                    <h1 class="uk-heading-primary uk-margin-remove  uk-text-primary" :class="{'blur-total': revenueLoading}">
                        <span v-if="metric == 'Revenue'">{{ totals.actuals | bigMoney }}</span>
                        <span v-else>{{ totals.actuals | commas }}</span>
                    </h1>
                </div>
                <div class="uk-text-center">
                    {{ metric | ucFirst }} Budget
                    <h1 class="uk-heading-primary uk-margin-remove  uk-text-primary" :class="{'blur-total': revenueLoading}">
                        <span v-if="metric == 'Revenue'">{{ totals.budget | bigMoney }}</span>
                        <span v-else>{{ totals.budget | commas }}</span>
                    </h1>
                </div>
                <div class="uk-text-center">
                    Variance
                    <h1 class="uk-heading-primary uk-margin-remove  uk-text-primary" :class="{'blur-total': revenueLoading}">
                        <span v-if="metric == 'Revenue'">{{ Math.round(totals.variance) | bigMoney }}</span>
                        <span v-else>{{ totals.variance | commas }}</span>
                    </h1>
                </div>
                <div class="uk-text-center">
                    Achieved
                    <h1 class="uk-heading-primary uk-margin-remove  uk-text-primary" :class="{'blur-total': revenueLoading}">
                        {{ totals.achieved | pct0 }}
                    </h1>
                </div>
            </vk-grid>
        </portal>

        <div v-if="selected" class="uk-margin-top">
            <revenue-chart :program="selected" :metric="'starts'" />
        </div>

        <vk-modal :show.sync="showModal">
            <vk-modal-title>{{ focusProgram }} </vk-modal-title>
            <vk-modal-close />
            <starts-modal :program="focusProgram"></starts-modal>
        </vk-modal>
    </div>
</template>

<script>
// import StartsModal from './StartsModal';
// import RevenueChart from '../overview/RevenueChart';

export default {
    name: 'StartsTable',

    components: {
        // StartsModal,
        // RevenueChart,
    },

    data() {
        return {
            revenue: {},
            metric: 'starts',
            metrics: ['starts', 'students', 'revenue'],
            columns: [
                'name', 
                'actuals', 
                'budget', 
                'variance', 
                'achieved'
            ],
            sort: 'actuals',
            order: 'desc',
            focusProgram: '',
            showModal: false,
        };
    },

    computed: {
        totals() {
            const sum = {
                actuals: 0,
                budget: 0,
                variance: 0,
                achieved: 0,
            };
            _.forEach(this.orderedList, item => {
                sum.actuals += this.revenueCellValue(item, 'actuals');
                sum.budget += this.revenueCellValue(item, 'budget');
            });
            sum.variance = sum.actuals - sum.budget;
            sum.achieved = (sum.budget > 0) ? sum.actuals / sum.budget : 0;
            return sum;
        },
        groupLabel() {
            let group = this.group;
            if (group == 'code') {
                return 'program';
            } else {
                return group;
            }
        },
        itemList() {
            if (this.selected != '' && this.selected != null) {
                return [this.selected];
            } else {
                return this.filteredGroupList;
            }
        },
        orderedList() {
            if (this.selected) {
                return [this.selected];
            }
            if (! this.sort) {
                return this.filteredGroupList;
            }
            let array = [];
            _.forEach(this.filteredGroupList, item => {
                let val = this.revenueCellValue(item, this.sort);
                val = String(val).replace('$', '').replace(/,/g, '');
                val = parseInt(val);

                const obj = {
                    name: item,
                    value: val
                }
                array.push(obj);
            });

            let order = _.orderBy(array, ['value'], [this.order]);
            let list = [];
            _.forEach(order, o => {
                list.push(o.name);
            });
            return list;
        }
        
    },

    methods: {

        getRevenueData() {
            axios
                .get('/data/revenue', {
                    params: {
                        filter: this.filter
                    }
                })
                .then(({data}) => {
                    this.revenue = data
                });
        },
        columnHeading(col) {
            if (col == 'name') {
                return this.groupLabel;
            } else if (col == 'actuals') {
                return 'forecast / actuals';
            } else {
                return col;
            }
        },
        columnClass(col) {
            if (col == 'name') {
                return 'clickable'
            } else {
                return 'clickable uk-text-center';
            }
        },

        setSort(col) {
            if (this.sort == col) {
                this.swapSort();
            } else {
                this.order = 'desc';
            }
            this.sort = col;
        },
        swapSort() {
            if (this.order == 'desc') {
                this.order = 'asc';
            } else {
                this.order = 'desc';
            }
        },

        revenueCellValue(pro, column) {
            let metric = this.revenueTable.metric;
            let actualsKey = metric.toLowerCase();
            let budgetKey = 'plan_' + metric.toLowerCase();

            let sumActuals = 0;
            let sumBudget = 0;

            let mine = _.filter(this.revenue, [this.group, pro]);
            _.forEach(mine, m => {
                if (actualsKey == 'revenue') {
                    sumActuals += parseFloat(m[actualsKey]) * 1000;
                    sumBudget += parseFloat(m[budgetKey]) * 1000;
                } else {
                    sumActuals += parseInt(m[actualsKey]);
                    sumBudget += parseInt(m[budgetKey]);
                }
            });
            switch (column) {
                case 'actuals':
                    return sumActuals;
                    break;
                case 'budget':
                    return sumBudget;
                    break;
                case 'variance':
                    return sumActuals - sumBudget;
                    break;
                case 'achieved':
                    return (sumBudget > 0) ? sumActuals / sumBudget : 0;
                    break;
                default:
                    return '';
                    break;
            }
        },

        revenueLastYearCellValue(pro, column) {
            let metric = this.revenueTable.metric;
            let actualsKey = metric.toLowerCase();
            let budgetKey = 'plan_' + metric.toLowerCase();

            let sumActuals = 0;
            let sumBudget = 0;

            let mine = _.filter(this.revenueLastYear, [this.group, pro]);
            _.forEach(mine, m => {
                if (actualsKey == 'revenue') {
                    sumActuals += parseInt(m[actualsKey]) * 1000;
                    sumBudget += parseInt(m[budgetKey]) * 1000;
                } else {
                    sumActuals += parseInt(m[actualsKey]);
                    sumBudget += parseInt(m[budgetKey]);
                }
            });
            switch (column) {
                case 'actuals':
                    return sumActuals;
                    break;
                case 'budget':
                    return sumBudget;
                    break;
                case 'variance':
                    return sumActuals - sumBudget;
                    break;
                case 'achieved':
                    return (sumBudget > 0) ? sumActuals / sumBudget : 0;
                    break;
                default:
                    return '';
                    break;
            }
        },

        tooltipContents(pro, column) {

            let metric = this.revenueTable.metric;

            let name = pro;
            let lastYear = this.revenueLastYear[name];
            let ly = this.termYear - 1;
            let lastYearValue = this.revenueLastYearCellValue(pro, column);
            let currentValue = this.revenueCellValue(pro, column);

            if (column == 'achieved') {
                lastYearValue = Math.round(lastYearValue * 100) + '%';
            }

            let string = `
                    ${ly} ${column}:  <div class="uk-float-right">${lastYearValue}</div>
                `;

            if (column == 'achieved') {
                return string;
            }

            let variance = currentValue - lastYearValue;
            let percentChange = (variance / lastYearValue * 100).toFixed(1);

            variance = variance.toLocaleString();

            string += `<br>YOY variance: `;
            if (metric == 'Revenue') {
                string += `$`;
            }
            string += `<div class="uk-float-right">${variance}</div>`;

            if (column == 'budget' || column == 'forecast') {
                if (isFinite(percentChange)) {
                    string += `<br> Pct. Change: <div class="uk-float-right">${percentChange}%</div>`;
                }
            }

            if (metric == 'Revenue' && column == 'actuals') {
                string += `<br> Pct. Change: <div class="uk-float-right">${percentChange}%</div>`;
            }

            return string;
        },

        cellClass(pro) {
            let rev = this.filteredRevenue[pro];
            if (!rev) {
                return '';
            }
            let metric = this.revenueTable.metric.toLowerCase();
            let key = metric + 'Achieved';
            let ratio = rev[key];

            if (ratio == 0) {
                return '';
            } else if (ratio > 1.1) {
                return 'table-success';
            } else if (ratio < 0.9) {
                return 'table-danger';
            } else {
                return '';
            }
        },

        thIcon(colName) {
            if (this.sort == colName) {
                if (this.order == 'asc') {
                    return '<i class="fa fa-sort-up color-blue" aria-hidden="true">';
                } else {
                    return '<i class="fa fa-sort-down color-blue" aria-hidden="true">';
                }
            } else {
                return '<i class="fa fa-sort color-blue-grey-lighter" aria-hidden="true">';
            }
        },

        showRevenueModal(program) {
            this.focusProgram = program;
            this.showModal = true;
        }
    },

    watch: {
        filterString() {
            this.getRevenueData();
        },
        termYear() {
            this.getRevenueData();
        },
        semester() {
            this.getRevenueData();
        },
        term() {
            this.getRevenueData();
        }
    },

    mounted() {
        this.getRevenueData();
    },
};
</script>

