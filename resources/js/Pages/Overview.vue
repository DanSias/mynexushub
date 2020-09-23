<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Overview
            </h2>
        </template>

        <OverviewFilter 
            @setFilter="setFilter"
        />

        <div class="container mx-auto my-6">
            <OverviewColumns 
                @setColumns="setColumns"
            />
        </div>
        
        <div class="flex">
            <div id="main-content">

                <div class="py-4" v-if="filterSet">
                    <div class="mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="py-4 px-6 border-b border-gray-200 capitalize text-gray-800">
                                Partnership KPIs by Program and Term
                            </div>
                            <OverviewTable 
                                :columns="columns"
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

import OverviewFilter from './Overview/Filter'
import OverviewTable from './Overview/Table'
import OverviewColumns from './Overview/Columns'

export default {
    name: 'OverviewHome',

    components: {
        AppLayout,
        OverviewFilter,
        OverviewTable,
        OverviewColumns
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
            filter: {},
            columns: {}
        }
    },

    computed: {
    },

    methods: {
        setFilter(filter) {
            this.filter = filter
            this.filterSet = true
        },
        setColumns(columns) {
            this.columns = columns
        },
    },
}
</script>
