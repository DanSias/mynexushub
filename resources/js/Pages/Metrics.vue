<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex justify-between">
                <span class="flex">
                    <span class="cursor-pointer mr-2" :class="(showFilter) ? 'text-blue-500' : ' text-gray-400'" @click="showFilter = ! showFilter" aria-label="Toggle Filter" data-balloon-pos="left">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    </span>
                    Metrics
                </span>
                <span class="flex">
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
                </span>
            </h2>
        </template>

        
        <div class="flex">

            <MetricsSidebar 
                v-show="showFilter"
                @setFilter="setFilter"
                :requestFilter="requestFilter"
            />


            <div id="main-content">
                
                <TimeTabs 
                    v-show="showDates"
                    class="container mx-auto mt-6"
                />
                <div class="px-6 py-8">
                    <portal-target name="MetricsTableTotalCards" />
                </div>

                <div class="py-4 w-full" v-if="filterSet">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="py-4 px-6 border-b border-gray-200 capitalize text-gray-800">
                                Marketing KPIs by {{ group }}
                            </div>
                            <MetricsTable 
                                :filter="filter" 
                            />
                        </div>
                    </div>
                </div>
            </div>

        </div>

        
    </app-layout>
</template>

<script>
import AppLayout from './../Layouts/AppLayout'

import MetricsSidebar from './Metrics/Sidebar'
import MetricsTable from './Metrics/Table'
import TimeTabs from '../Components/Time/Tabs'

export default {
    name: 'MetricsHome',

    components: {
        AppLayout,
        MetricsSidebar,
        MetricsTable,
        TimeTabs,
    },

    props: {
        requestKey: {
            type: String,
            default: ''
        },
        requestFilter: {
            type: Object,
            default: () => {}
        }
    },

    data() {
        return {
            filterSet: false,
            showFilter: false,
            showDates: false,
            filter: {}
        }
    },

    computed: {
        group() {
            return (this.filter.group) ? this.filter.group : ''
        }
    },

    methods: {
        setFilter(filter) {
            this.filter = filter
            this.filterSet = true
        }
    },

    mounted() {
        // this.filter = this.requestFilter
    }

}
</script>
