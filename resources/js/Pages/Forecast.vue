<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex justify-between">
                <div class="flex">
                    <!-- Page Title -->
                    <span class="flex -ml-4">
                        <inertia-link 
                            v-if="program" 
                            :href="(channel) ? `/forecast/${program}` : '/forecast'" 
                            class="text-gray-200 hover:text-gray-600 duration-200 mr-2" 
                            data-balloon-pos="right" 
                            :aria-label="(channel) ? `All ${program} Channels` : 'All Forecasts'"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18"></path></svg>
                        </inertia-link>
                        
                        {{ program }} {{ channel }} <FilterText v-if="! program && ! channel" :filter="filter" class="mr-1" />  Marketing Forecast
                        
                        <span v-show="! program && ! channel" @click="showFilter = ! showFilter" class="ml-2 duration-200 cursor-pointer" :class="(showFilter) ? 'text-blue-500 hover:text-blue-700' : 'text-gray-300 hover:text-gray-600'" data-balloon-pos="right" :aria-label="(showFilter) ? 'Hide Program Filter' : 'Show Program Filter'">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        </span>
                        <span ref="trigger" @click="toggleLink()" v-show="program || channel" class="ml-2 duration-200 cursor-pointer" :class="(showLink) ? 'text-blue-500 hover:text-blue-700' : 'text-gray-300 hover:text-gray-600'" data-balloon-pos="right" aria-label="Update Forecast View">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        </span>
                        <!-- Program / Channel Link Popover -->
                        <div 
                            ref="pop" 
                            :class="(showLink) ? 'block' : 'hidden'" 
                            class="border border-gray-200 mt-3 block z-50 text-sm shadow-xl text-left no-underline break-words "
                        >
                            <div>
                                <div class="bg-gray-50 text-gray-800 font-semibold p-3 mb-0 border-b border-solid border-gray-200 capitalize">
                                    Change Forecast View
                                </div>
                                <div class="bg-white p-3 flex">
                                    <div class="mr-2 w-56">
                                        <ProgramSelect
                                            class="w-full"
                                            v-model="link.program"
                                            :multiple="false"
                                            :filter="filter"
                                        />
                                    </div>
                                    <div class="mr-2 w-56">
                                        <ChannelSelect
                                            class="w-full" 
                                            v-model="link.channel"  
                                            :multiple="false"  
                                        /> 
                                    </div>
                                    <!-- <button @click="updateLink()" class="bg-gray-800 px-5 py-2 text-white rounded focus:outline-none col-span-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                                    </button> -->
                                    <inertia-link :href="`/forecast/${link.program}/${link.channel}`">
                                        <button  class="bg-gray-800 px-5 py-2 text-white rounded focus:outline-none col-span-2">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                                        </button>
                                    </inertia-link>
                                </div>
                            </div>
                        </div>

                        </span>
                    </div>
                <!-- Status Text -->
                <span>
                    <portal-target name="ChannelApprovalDate" />
                </span>
                <!-- Timeframe Text -->
                <span class="text-gray-500 capitalize font-light flex">
                    <span v-if="status == 'open'">{{ month }} - December {{ year }}</span>
                    <span v-if="status == 'closed'" class="text-red-500 ml-2" data-balloon-pos="left" aria-label="Forecasting is Closed"> <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                </span>
            </h2>
        </template>

        <div v-if="showFilter" class="container mx-auto bg-white rounded shadow mt-8 p-6">
            <div class="grid grid-cols-4 gap-4">
                <div class="w-full">
                    <LocationSelect 
                        v-model="filter.location" 
                    />
                </div>
                <div class="w-full">
                    <BuSelect 
                        v-model="filter.bu" 
                        :filter="filter"
                    />
                </div>
                <div class="w-full">
                    <PartnerSelect 
                        v-model="filter.partner" 
                        :filter="filter"
                    />
                </div>
                <div class="w-full">
                    <ChannelSelect 
                        v-model="filter.channel" 
                    />
                </div>
            </div>
        </div>

        <div v-if="filterSet">
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
                :approver="approver"
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
        </div>
        <div class="absolute left-6 top-40" v-if="lastProgram">
            <inertia-link :href="`/forecast/${lastProgram}/${channel}`">
                <button class="bg-gray-50 border border-gray-300 text-gray-400 rounded hover:bg-gray-800 duration-200 hover:text-white p-2" data-balloon-pos="right" :aria-label="lastProgram">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
            </inertia-link>
        </div>
        <div class="absolute right-6 top-40" v-if="nextProgram">
            <inertia-link :href="`/forecast/${nextProgram}/${channel}`">
                <button class="bg-gray-50 border border-gray-300 text-gray-400 rounded hover:bg-gray-800 duration-200 hover:text-white p-2" data-balloon-pos="left" :aria-label="nextProgram">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </inertia-link>
            </div>
    </app-layout>
</template>

<script>
import Popper from "popper.js";
import AppLayout from './../Layouts/AppLayout'
import ForecastIndex from '../Pages/Forecast/Index'
import ForecastProgram from '../Pages/Forecast/Program'
import ForecastChannel from '../Pages/Forecast/Channel'

// Filter
import FilterText from '../Components/FilterText'
import LocationSelect from '../Components/Select/Location'
import BuSelect from '../Components/Select/Bu'
import PartnerSelect from '../Components/Select/Partner'
import ChannelSelect from '../Components/Select/Channel'
import ProgramSelect from '../Components/Select/Program'

export default {
    name: 'ForecastHome',

    metaInfo() {
        return {
            title: `${this.program} ${this.channel} Forecasting`
        }
    },

    components: {
        AppLayout,
        ForecastIndex,
        ForecastProgram,
        ForecastChannel,
        FilterText,
        LocationSelect,
        BuSelect,
        PartnerSelect,
        ChannelSelect,
        ProgramSelect
    },

    props: {
        requestFilter: {
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
        },
        role: {
            type: Object,
            default: () => {}
        },
    },

    data() {
        return {
            showFilter: false,
            filterSet: false,
            filter: {
                active: ['TRUE'],
                location: [],
                bu: [],
                partner: [],
                channel: []
            },
            showLink: false,
            link: {
                program: '',
                channel: ''
            },
            programList: []
        }
    },

    computed: {
        group() {
            return (this.filter.group) ? this.filter.group : ''
        },
        approver() {
            if (! this.role.team == 'Media & Analytics') {
                return false;
            }
            if (this.role.title == 'Associate Director' || this.role.title == 'neXus Admin') {
                return true;
            }
            return false;
        },
        programKey() {
            return _.indexOf(this.programList, this.program)
        },
        nextProgram() {
            return this.programList[this.programKey + 1]
        },
        lastProgram() {
            return this.programList[this.programKey - 1]
        }
    },

    methods: {
        toggleLink() {
            if(this.showLink){
                this.showLink = false
            } else {
                this.showLink = true
                new Popper(this.$refs.trigger, this.$refs.pop, {
                placement: "bottom"
                })
            }
        },
        newLink() {
            `/forecast/${this.link.program}/${this.link.channel}`
        },
        getProgramList() {
            axios
                .get('/data/programs/list/code', {
                    params: {
                        filter: this.filter
                    }
                })
                .then(({data}) => {
                    this.programList = data
                })
        }
    },

    mounted() {
        if (this.requestFilter.location) {
            this.filter.location = this.requestFilter.location
        }
        if (this.requestFilter.bu) {
            this.filter.bu = this.requestFilter.bu
        }
        if (this.requestFilter.partner) {
            this.filter.partner = this.requestFilter.partner
        }
        if (this.requestFilter.channel) {
            this.filter.channel = this.requestFilter.channel
        }
        this.filterSet = true

        if (this.program) {
            this.link.program = this.program
            this.getProgramList()
        }
        if (this.channel) {
            this.link.channel = this.channel
        }
    }
}
</script>
