<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex justify-between">
                <span>{{ program }} {{ channel }} Marketing Forecast</span>
                <span class="text-gray-500 capitalize font-light">{{ month }} - December {{ year }}</span>
            </h2>
        </template>
        
        <ForecastIndex 
            v-if="program == ''"
            class="container mx-auto"
            :filter="filter"
            :year="year"
            :month="month"
        />

        <ForecastProgram 
            v-if="program != '' && channel == ''"
            class="container mx-auto"
            :program="program"
            :filter="filter"
            :year="year"
            :month="month"
        />

        <ForecastChannel
            v-if="program && channel"
            class="container mx-auto"
            :program="program"
            :channel="channel"
            :filter="filter"
            :year="year"
            :month="month"
            :status="status"
        />
        <!-- <div class="flex">
            <div id="main-content">
                <div class="py-4">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="py-4 px-6 border-b border-gray-200 capitalize text-gray-800">
                                Marketing KPIs by {{ group }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

    </app-layout>
</template>

<script>
import AppLayout from './../Layouts/AppLayout'
import ForecastIndex from '../Pages/Forecast/Index'
import ForecastProgram from '../Pages/Forecast/Program'
import ForecastChannel from '../Pages/Forecast/Channel'

export default {
    name: 'ForecastHome',

    components: {
        AppLayout,
        ForecastIndex,
        ForecastProgram,
        ForecastChannel
    },

    props: {
        filter: {
            type: Object,
            default: () => {}
        },
        year: {
            type: Number,
            default: 0
        },
        month: {
            type: String,
            default: ''
        },
        status: {
            type: String,
            default: ''
        },
        program: {
            type: String,
            default: ''
        },
        channel: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            filterSet: false,
        }
    },

    computed: {
        group() {
            return (this.filter.group) ? this.filter.group : ''
        }
    },

    methods: {
        // setFilter(filter) {
        //     this.filter = filter
        //     this.filterSet = true
        // }
    },

    mounted() {
        // this.filter = this.requestFilter
    }

}
</script>
