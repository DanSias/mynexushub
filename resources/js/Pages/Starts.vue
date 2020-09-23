<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Metrics
            </h2>
        </template>
        
        <div class="flex">

            <MetricsSidebar 
                @setFilter="setFilter"
                :requestFilter="requestFilter"
            />

            <div id="main-content">
                <div class="px-6 py-8">
                    <portal-target name="MetricsTableTotals" />
                </div>

                <div class="py-4" v-if="filterSet">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="py-4 px-6 border-b border-gray-200 capitalize text-gray-800">
                                Marketing KPIs by {{ group }}
                            </div>
                            <StartsTable 
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
import StartsTable from './Starts/Table'

export default {
    name: 'StartsHome',

    components: {
        AppLayout,
        MetricsSidebar,
        StartsTable
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
