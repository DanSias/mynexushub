<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex justify-between">
                <span class="flex">
                    <!-- <span class="cursor-pointer mr-2" :class="(showFilter) ? 'text-blue-500' : ' text-gray-400'" @click="showFilter = ! showFilter" aria-label="Toggle Filter" data-balloon-pos="left">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    </span> -->
                    Administration
                </span>
                <!-- <span class="flex">
                    <span class="cursor-pointer mr-2" :class="(showDates) ? 'text-blue-500' : ' text-gray-500'" @click="showDates = ! showDates" aria-label="Change Date Range" data-balloon-pos="left">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </span>
                    Month Text
                </span>
                <span class="flex">
                    <span class="cursor-pointer mr-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </span>
                    Table Layout
                </span> -->
            </h2>
        </template>

        <div class="container mx-auto" v-if="canView">
            <div class="bg-white rounded shadow w-full mt-6">
                <div class="border-b border-gray-300 px-6 py-4 text-semibold">
                    <h4 >Tables Last Updated:</h4>
                </div>
                <div class="p-6 grid grid-cols-4 gap-4">
                    <div v-for="(date, i) in showDates" :key="i" class="uk-text-center">
                        <span v-if="date.updated">
                            <strong>{{ date }}</strong><br>
                            {{ dates[date].updated | dateFj }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- <admin-merge class="uk-margin-top" /> -->

            <h2 class="mt-8 text-2xl text-bold">Forecast &amp; Budget Inputs</h2>
            <div class="grid grid-cols-2 gap-6 mt-4">
                <!-- Forecast -->
                <div class="bg-white rounded shadow w-full">
                    <div class="border-b px-6 py-4 text-semibold flex justify-between" :class="(forecastSettings.status == 'open') ? 'border-green-400' : 'border-gray-300'">
                        Forecast Status
                        <span v-if="forecastSettings.status == 'open'" class="text-green-500" data-balloon-pos="left" aria-label="Forecast Inputs are Open">
                            <svg class="check-circle w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </span>
                        <span v-else class="text-red-500" data-balloon-pos="left" aria-label="Forecast Inputs are Closed">
                            <svg class="x-circle w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        </span>
                    </div>
                    <div class="p-6 flex">
                        <div class="flex-grow">
                            <strong>Year: </strong> {{ forecastSettings.year }}<br>
                            <strong>Month: </strong> <span class="capitalize">{{ forecastSettings.month }}</span>
                        </div>
                        <div>
                            <button class="focus-outline-none flex text-gray-500" @click="showForecastModal()">
                                <svg class="w-6 h-6 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path></svg>
                                Edit
                            </button>
                        </div>
                    </div>
                    <div class="bg-gray-50">
                        <div class="flex justify-between p-4">
                            <div>
                                <a href="/excel/forecast" target="_blank" class="flex text-gray-400 hover:text-gray-800 duration-200" data-balloon-pos="right" aria-label="Export all approved forecasts to Excel">
                                    <svg class=" mr-1 w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    Export
                                </a>
                            </div>
                            <div class="flex text-gray-400 hover:text-gray-800 duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <a href="#" @click.prevent="checkForecastPrograms()" class=""> Check Programs</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Budget -->
                <div class="bg-white rounded shadow w-full">
                    <div class="border-b px-6 py-4 text-semibold flex justify-between" :class="(budgetSettings.status == 'open') ? 'border-green-400' : 'border-gray-300'">
                        Budget Status
                        <span v-if="budgetSettings.status == 'open'" class="text-green-500" data-balloon-pos="left" aria-label="Budget Inputs are Open">
                            <svg class="check-circle w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </span>
                        <span v-else class="text-red-500" data-balloon-pos="left" aria-label="Budget Inputs are Closed">
                            <svg class="x-circle w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        </span>
                    </div>
                    <div class="p-6 flex">
                        <div class="flex-grow">
                            <strong>Year: </strong> {{ budgetSettings.year }}<br>
                            <strong>Scenario: </strong> <span class="capitalize">{{ budgetSettings.scenario }}</span>
                        </div>
                        <div>
                            <button class="focus-outline-none flex text-gray-500" @click="showBudgetModal()">
                                <svg class="w-6 h-6 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path></svg>
                                Edit
                            </button>
                        </div>
                    </div>
                    <div class="bg-gray-50">
                        <div class="flex justify-between p-4">
                            <div>
                                <a href="/excel/budget" class="flex text-gray-400 hover:text-gray-800 duration-200" data-balloon-pos="right" aria-label="Export all budgets to Excel">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    Export
                                </a>
                            </div>
                            <div>
                                <!-- <a href="#" @click.prevent="checkForecastPrograms()"> Check Programs</a> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div>
                <h2 class="mt-8 my-4 text-2xl">Merge Data</h2>
                <div class="flex -mx-3 mt-4">
                    <div v-for="(m, i) in merge" :key="i" class="bg-white shadow rounded px-6 py-6 flex-grow mx-3">
                        <div class="text-center">
                            <a target="_blank" :href="'/' + m.label + '/merge'">
                                <button type="text" :aria-label="m.tooltip" data-balloon-pos="bottom" class="flex text-lg capitalize">
                                    <svg class="mr-2 text-gray-300 w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path><path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path></svg>
                                    {{ m.label }}
                                </button>
                            </a>
                        </div>
                    </div>
                </div>

                <h2 class="mt-8 my-4 text-2xl">Check Program Codes</h2>
                <div class="uk-child-width-expand flex -mx-3 mt-4">
                    <div v-for="(c, j) in codes" :key="j" class="flex-grow bg-white shadow rounded mx-3 px-6 py-6">
                        <div class="text-center">
                            <button type="text" @click="checkCodes(c)" class="flex text-lg capitalize">
                                <svg class="mr-2 text-gray-300 w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path><path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path></svg>
                                {{ c }}
                            </button>
                        </div>
                    </div>
                </div>
<!-- 
                <vk-modal :show.sync="showModal">
                    <vk-modal-close @click="showModal = false"></vk-modal-close>
                    <vk-modal-title>{{ codeType }} codes</vk-modal-title>
                    <span v-show="codeResponse.updated && codeResponse.updated.length > 0">
                        <p>Updated Codes</p>
                        <ul>
                            <li v-for="(code, i) in codeResponse.updated" :key="i">
                                {{ code }}
                            </li>
                        </ul>
                    </span>
                    <span v-show="codeResponse.missing && codeResponse.missing.length > 0">
                        <p>Missing Codes (Add Map)</p>
                        <ul>
                            <li v-for="(code, j) in codeResponse.missing" :key="j">
                                {{ code }}
                            </li>
                        </ul>
                    </span>
                    <span v-show="codeResponse.maps && codeResponse.maps.length > 0">
                        <p>Updated Codes (from Maps)</p>
                        <ul>
                            <li v-for="(code, k) in codeResponse.maps" :key="k">
                                {{ code }}
                            </li>
                        </ul>
                    </span>
                </vk-modal> -->
            </div>

            <!-- <p>&nbsp;</p>

            <vk-modal :show.sync="showBudgetModal"  size="container">
                <vk-modal-title>Budget Settings</vk-modal-title>
                <vk-modal-close @click="showBudgetModal = false"></vk-modal-close>
                <budget-settings 
                    @saved="budgetSaved()"
                />
                <p>&nbsp;</p>
            </vk-modal>

            <vk-modal :show.sync="showForecastModal"  size="container">
                <vk-modal-title>Forecast Settings</vk-modal-title>
                <vk-modal-close @click="showForecastModal = false"></vk-modal-close>
                <forecast-settings 
                    @saved="forecastSaved()"
                />
                <p>&nbsp;</p>
            </vk-modal>


            <vk-modal :show.sync="showForecastCheckModal"  size="container">
                <vk-modal-close @click="showBudgetModal = false"></vk-modal-close>

                <ul class="uk-tab uk-child-width-expand edit-modal-tabs">
                    <li :class="{'uk-active': tab == 'approvals' }">
                        <a href="#" @click.prevent="tab = 'approvals'">No Approved Forecasts</a>
                    </li>
                    <li :class="{'uk-active': tab == 'awaiting' }">
                        <a href="#" @click.prevent="tab = 'awaiting'">Awaiting Approval</a>
                    </li>
                </ul>
                <div v-show="tab == 'approvals'">
                    <div>
                        <div class="uk-width-expand">
                            <h3>Active Programs Without Approved Forecasts</h3>
                        </div>
                        <div class="uk-width-1-5 uk-margin-right">
                            <input type="text" v-model="query" class="uk-input" placeholder="Search...">
                        </div>
                    </div>
                    <div divided class="uk-child-width-1-5">
                        <div v-for="(code, i) in programsWithoutForecast" :key="i" class="uk-text-small uk-margin-top">
                            <span class="clickable uk-text-muted" @click="goForecast(code)">{{ code }}</span>
                        </div>
                    </div>
                </div>
                <div v-show="tab == 'awaiting'">
                    <div divided class="uk-child-width-1-5">
                        <div v-for="(code, i) in awaiting" :key="i" class="uk-text-small uk-margin-top">
                            <span class="clickable uk-text-muted" @click="goForecast(code)">{{ code }}</span>
                        </div>
                    </div>
                </div>
                <p>&nbsp;</p>
            </vk-modal> -->
        </div>


        <!-- Feedback and Help Modal -->
        <BaseModal v-if="modalVisible" @close="modalVisible = false" scrollable :title="modalTitle">
            <div v-if="modalType == 'forecast'">
                <ForecastSettings 
                    class="my-6" 
                    :settings="forecastSettings"
                    @saved="getForecastSettings(); modalVisible = false"
                />
            </div>
            <div v-else>
                <BudgetSettings 
                    class="my-6" 
                    :settings="budgetSettings"
                    @saved="getBudgetSettings(); modalVisible = false"
                />
            </div>
            <template v-slot:footer>
                <!-- <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                    <button
                        type="button"
                        class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-red-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                    >
                        Deactivate
                    </button>
                </span> -->
            </template>
        </BaseModal>
    </app-layout>
</template>


<script>
import AppLayout from './../Layouts/AppLayout'
import BaseModal from '../Components/BaseModal'
import ForecastSettings from './Forecast/Settings'
import BudgetSettings from './Budget/Settings'
// import AdminMerge from './Merge'

export default {
    name: 'neXusAdmin',

    metaInfo() {
        return {
            title: `neXus Administration`,
        } 
    },
    
    components: {
        AppLayout,
        ForecastSettings,
        BaseModal,
        BudgetSettings,
        // AdminMerge,
    },

    data() {
        return {
            modalVisible: false,
            modalType: 'forecast',
            dates: {},
            budgetSettings: {},
            showForecastCheckModal: false,
            forecastSettings: {},
            hasForecasts: [],
            awaiting: [],
            query: '',
            tab: 'approvals',
            merge: {
                revenue: {
                    label: 'Revenue',
                    tooltip: 'Revenue report'
                },
                cvrs: {
                    label: 'CVRS',
                    tooltip: 'Lead to start conversion ates based on latest forecasts'
                },
                conversica: {
                    label: 'Conversica',
                    tooltip: 'Merge data from Conversica tables into metric tables'
                },
                deadlines: {
                    label: 'Deadlines',
                    tooltip: 'Move deadlines_raw table into deadlines and format'
                },
            },

            codes: [
                'deadlines',
                'revenue',
                'term-conversion'
            ],
            codeResponse: {},
            codeType: '',
            showModal: false,
        }
    },
    

    computed: {
        modalTitle() {
            return this.modalType + ' Settings'
        },
        canView() {
            return true
        },
        showDates() {
            let date = new Date();
            return [
                'monthly_actuals_' + date.getFullYear(),
                'monthly_forecast_' + date.getFullYear(),
                'monthly_budget_' + date.getFullYear(),
                'monthly_metrics',
                'revenue_report',
                'term_conversion',
                'conversica',
                'conversica_funnel',
            ]
        },
        programsWithoutForecast() {
            let missing = _.difference(this.activeProgramsList, this.hasForecasts);
            if (this.query) {
                missing = _.remove(missing, m => {
                    return m.toLowerCase().includes(this.query.toLowerCase());
                })
            }

            return missing;
        },
    },

    methods: {
        showForecastModal() {
            this.modalVisible = true
            this.modalType = 'forecast'
        },
        showBudgetModal() {
            this.modalVisible = true
            this.modalType = 'budget'
        },
        checkCodes(type) {
            axios
                .get('/data/' + type + '/codes')
                .then(({data}) => {
                    this.codeResponse = data;
                    this.codeType = type;
                    this.showModal = true;
                })
        },
        getBudgetSettings() {
            axios
                .get('/data/budget/settings')
                .then(({data}) => this.budgetSettings = data);
        },
        getForecastSettings() {
            axios
                .get('/data/forecast/settings')
                .then(({data}) => this.forecastSettings = data);
        },
        budgetSaved() {
            this.getBudgetSettings();
            this.showBudgetModal = false;
        },
        forecastSaved() {
            this.getForecastSettings();
            this.showForecastModal = false;
        },
        checkForecastPrograms() {
            console.log('check programs');
            this.showForecastCheckModal = true;
            axios
                .get('/api/forecast/check/programs')
                .then(({data}) => {
                    this.hasForecasts = data;
                });
            axios   
                .get('/api/forecast/check/awaiting')
                .then(({data}) => {
                    this.awaiting = data;
                });
        },
        goForecast(program) {
            const router = this.$router;
            router.push('/forecast/' + program);
        }
    },

    mounted() {
        this.getBudgetSettings();
        this.getForecastSettings();
    }

}
</script>

<style>

</style>