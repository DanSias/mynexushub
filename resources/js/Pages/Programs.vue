<template>
    <app-layout>
        <!-- Heading Bar -->
        <template #header>
            <div class="flex items-center justify-between h-18">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <!-- All Programs -->
                    <span v-if="view == 'collection'">
                        <div class="flex">
                            Degree Programs
                            <a href="/excel/attributes" target="_blank" data-balloon-pos="down" aria-label="Download to Excel" class="text-gray-500 hover:text-blue-500 ml-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                        </div>
                    </span>
                    <!-- Edit Program -->
                    <span v-else-if="action == 'edit'"  class="flex">
                        <inertia-link :href="`/programs/${program}`" class="mr-4 text-gray-400 hover:text-gray-700 transition duration-500" aria-label="Back to Program" data-balloon-pos="down-left">
                            <svg class="w-6 h-6 " fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path></svg>                    
                        </inertia-link>
                        <span class="mr-2">Edit </span> {{ program }} Program Details
                    </span>
                    <!-- New Program -->
                    <span v-else-if="view == 'new'" class="flex">
                        <inertia-link href="/programs" class="mr-4 text-gray-400 hover:text-gray-700 transition duration-500" aria-label="Back to Programs Table" data-balloon-pos="down-left">
                            <svg class="w-6 h-6 " fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path></svg>                    
                        </inertia-link>
                        Add New Program
                    </span>
                    <!-- Single Program -->
                    <span v-else-if="view == 'single'" class="flex" >
                        <inertia-link href="/programs" class="mr-4 text-gray-400 hover:text-gray-700 transition duration-500" aria-label="Back to Programs Table" data-balloon-pos="down-left">
                            <svg class="w-6 h-6 " fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path></svg>                    
                        </inertia-link>
                        <span v-if="action == 'edit'" class="mr-2">Edit </span> {{ program }} Program Details
                    </span>
                </h2>
                <div class="flex" v-show="view != 'new'">
                    <!-- Filter Toggle -->
                    <span v-if="view == 'collection'" aria-label="Filter Programs" data-balloon-pos="down" class="mr-4" :class="(showFilter) ? 'text-blue-500' : 'text-gray-500 hover:text-blue-500'" @click="showFilter = ! showFilter">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    </span>
                    <!-- Column Toggle -->
                    <span v-if="view == 'collection'" aria-label="Edit Table Columns" data-balloon-pos="down" class="mr-4" :class="(showColumns) ? 'text-blue-500' : 'text-gray-500 hover:text-blue-500'" @click="showColumns = ! showColumns">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </span>
                    <!-- New Program Icon -->
                    <inertia-link v-if="view != 'single' && action != 'edit'" href="/programs/new" aria-label="Add New Program" data-balloon-pos="down" class="text-gray-500 hover:text-blue-500">
                        <svg class="w-6 h-6 " fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </inertia-link>
                    <!-- Edit Icon -->
                    <inertia-link v-if="view == 'single' && action != 'edit'" :href="`/programs/${program}/edit`" aria-label="Edit Program" data-balloon-pos="down" class="text-gray-500 hover:text-blue-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </inertia-link>

                    <span v-if="action == 'edit'" class="text-sm">
                        <span 
                            v-for="(type, i) in formTypes" :key="i" 
                            class="mr-5 capitalize transition-all duration-100 cursor-pointer px-3 py-1 border text-semibold" 
                            :class="(formType == type) ? 'border-blue-500 rounded-full bg-blue-500 text-white' : 'border-white bg-white text-gray-500'"
                            @click="formType = type"
                        >
                            {{ type }}
                        </span>
                    </span>
                </div>
            </div>
        </template>


        <!-- Programs Table -->
        <ProgramTable 
            v-if="view =='collection'"
            :showColumns="showColumns"
            :showFilter="showFilter"
            :urlFilter="filter"
            :programs="programs"
        />

        <!-- Single Program Cards -->
        <div v-if="view == 'single' && action != 'edit'" class="container mx-auto grid gap-5 grid-cols-3 my-8">
            <ProgramDetails 
                class="bg-white rounded p-6"
                :program="programs[0]"
            />
            <ProgramRequirements 
                class="bg-white rounded p-6"
                :requirements="programs[0].requirements"
            /> 
            <ProgramPartner 
                class="bg-white rounded p-6"
                :partner="programs[0].partner"
            />
        </div>

        <!-- Program Form -->
        <div v-if="view == 'new'">
            <ProgramForm />
        </div>

        <!-- Edit -->
        <div v-if="action == 'edit'">
            <EditProgram
                :program="programs[0]"
                :formType="formType"
            />
        </div>

    </app-layout>
</template>

<script>
import AppLayout from './../Layouts/AppLayout'
import ProgramRequirements from './Programs/Requirements'
import ProgramDetails from './Programs/Details'
import ProgramTable from './Programs/Index'
import ProgramPartner from './Programs/Partner'
import ProgramForm from './Programs/Form'
import EditProgram from './Programs/Edit'

export default {
    name: 'Programs',

    metaInfo() {
        if (this.program == '') {
            return {
                title: `Degree Program Attributes`,
            } 
        } else {
            return {
                title: `${this.program} Program Attributes`
            }
        }
    },

    props: {
        filter: {
            type: Object,
            default: () => {}
        },
        program: {
            type: String,
            default: ''
        },
        programs: {
            type: Array,
            default: () => []
        },
        action: {
            type: String,
            default: ''
        }
    },

    components: {
        AppLayout,
        ProgramDetails,
        ProgramTable,
        ProgramRequirements,
        ProgramPartner,
        
        ProgramForm,
        EditProgram
    },

    data() {
        return {
            showColumns: false,
            showFilter: false,
            formType: 'attributes',
            formTypes: ['attributes', 'requirements', 'tracks', 'codes']
        }
    },

    computed: {
        filterPartnerIds() {
            let array = []
            _.forEach(this.filter.partner, partner => {
                array.push(partner.id)
            })
            return array
        },
        view() {
            if (this.program == 'new') {
                return 'new'
            } else if (this.program != '') {
                return 'single'
            } else {
                return 'collection'
            }
        }
    },

}
</script>
