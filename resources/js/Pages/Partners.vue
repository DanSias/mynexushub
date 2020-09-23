<template>
    <app-layout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <span v-if="partner">{{ partner }} Partner Details</span>
                    <span v-else>Academic Partners</span>
                </h2>
                
                <div class="flex">

                    <!-- Filter Toggle -->
                    <span aria-label="Change Table Columns" data-balloon-pos="down" class="mr-4" :class="(showColumns) ? 'text-blue-500' : 'text-gray-500 hover:text-blue-500'" @click="showColumns = ! showColumns">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </span>

                    <!-- Add Partner -->
                    <span aria-label="Add New Partner" data-balloon-pos="down" class="text-gray-500 hover:text-blue-500">
                        <svg class="w-6 h-6 " fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                </div>
            </div>
        </template>

        <!-- Column Checkboxes -->
        <div v-if="showColumns" class="container mx-auto mt-8 bg-white rounded shadow">
            <p class="text-gray-800 text-semibold text-lg py-4 px-6 border-b border-gray-200 flex">
                <span class="flex-grow">Visible Table Columns</span>
                <span class="text-gray-400 hover:text-gray-700 cursor-pointer" @click="showColumns = false">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </span>
            </p>
            <div class="grid gap-4 grid-cols-8 p-6">
                <div v-for="(value, name, i) in columns" :key="i">
                    <input type="checkbox" v-model="columns[name]"> 
                    <span v-if="name == 'active_programs_count'">Programs</span>
                    <span v-else class="capitalize">{{ name.replace('_', ' ') }}</span>
                </div>
            </div>
        </div>

        <div class="container mx-auto mt-8 px-6 py-8 bg-white rounded shadow">
            <table class="table-auto">
                <thead>
                    <tr class="text-gray-500">
                        <th v-for="(col, i) in visibleColumns" :key="i" class="px-4 py-2 capitalize">
                            <span v-if="col == 'active_programs_count'">Programs</span>
                            <span v-else>{{ col.replace('_', ' ') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr 
                        v-for="(partner, j) in partners" :key="j" 
                        class="hover:bg-yellow-50"
                        :class="(j % 2 == 0) ? 'bg-gray-50' : ''"
                    >
                        <td 
                            v-for="(col, i) in visibleColumns" :key="i" 
                            class="border px-4 py-2"
                            :class="(col == 'active_programs_count') ? 'text-right' : ''"
                        >
                            <span v-html="formatCell(partner[col], col)"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </app-layout>
</template>

<script>
import AppLayout from './../Layouts/AppLayout'

export default {
    name: 'Partners',

    metaInfo() {
        if (this.partner == '') {
            return {
                title: `Academic Partners`,
            } 
        } else {
            return {
                title: `${this.partner} Partner Attributes`
            }
        }
    },

    props: {
        partner: {
            type: String,
            default: ''
        },
        partners: {
            type: Array,
            default: () => []
        }
    },

    components: {
        AppLayout,
    },

    data() {
        return {
            showColumns: false,
            columns: {
                code: true,
                school: true,
                type: true,
                url: true,
                facebook: true,
                zip: true,
                region: true,
                state: true,
                latitude: false,
                longitude: false,
                time_zone: false,
                school_id: false,
                notes: false,
                contacts: false,
                active_programs_count: true
            }
        }
    },

    computed: {
        visibleColumns() {
            let array = []
            _.forEach(this.columns, (value, col) => {
                if (value) {
                    array.push(col)
                }
            })
            return array
        }
    },

    methods: {
        formatCell(text, col) {
            if (text == null || text == '') {
                return ''
            }
            if (col == 'type') {
                return text.charAt(0).toUpperCase() + text.slice(1);
            } else if (col == 'url') {
                return `<a href="http://${text}" target="_blank" class="text-blue-500 hover:underline hover:text-blue-600">${text}</a>`
            } else if (col == 'facebook') {
                return `<a href="https://facebook.com/${text}" target="_blank" class="text-blue-500 hover:underline hover:text-blue-600">${text}</a>`
            } else if (col == 'notes') {
                return `
                    <span>
                        <svg class="ml-2 w-6 h-6 text-center text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </span>
                `
            } else if (col == 'contacts') {
                return `<svg class="tooltip ml-4 w-6 h-6 text-center text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
            } else if (col == 'active_programs') {
                return `<svg class="tooltip ml-4 w-6 h-6 text-center text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
            }
            return text
        }
    },
}
</script>
