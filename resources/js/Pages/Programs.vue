<template>
    <app-layout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <span v-if="program">{{ program }} Program Details</span>
                    <span v-else>Degree Programs</span>
                </h2>
                <div class="flex">
                    <span aria-label="Add New Program" data-balloon-pos="down" class="text-gray-500 hover:text-blue-500">
                        <svg class="w-6 h-6 " fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                </div>
            </div>
        </template>

        <!-- Column Checkboxes -->
        <div v-show="program == ''" class="container mx-auto mt-8 p-6 bg-white rounded shadow">
            <div class="grid gap-4 grid-cols-8">
                <div v-for="(value, name, i) in columns" :key="i">
                    <input type="checkbox" v-model="columns[name]"> 
                    <span class="capitalize">{{ name.replace('_', ' ') }}</span>
                </div>
            </div>
        </div>

        <!-- Programs Table -->
        <div class="container mx-auto mt-8 px-6 py-8 bg-white rounded shadow">
            <table class="table-auto">
                <thead>
                    <tr class="text-gray-500">
                        <th v-for="(col, i) in visibleColumns" :key="i" class="px-4 py-2 capitalize">
                            {{ col.replace('_', ' ') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr 
                        v-for="(program, j) in programs" :key="j" 
                        class="hover:bg-yellow-50"
                        :class="(j % 2 == 0) ? 'bg-gray-50' : ''"
                    >
                        <td 
                            v-for="(col, i) in visibleColumns" :key="i" 
                            class="border px-4 py-2"
                        >
                            <span v-html="formatCell(program[col], col)"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Single Program Cards -->
        <div v-if="program" class="container mx-auto grid gap-4 grid-cols-3 mt-8">
            <ProgramDetails 
                class="bg-white rounded px-4 py-6"
                :program="programs[0]"
            />
            <ProgramRequirements 
                class="bg-white rounded px-4 py-6"
                :requirements="programs[0].requirements"
            /> 
            <ProgramPartner 
                class="bg-white rounded px-4 py-6"
                :program="programs[0]"
            />
        </div>

    </app-layout>
</template>

<script>
    import AppLayout from './../Layouts/AppLayout'
    import ProgramRequirements from './Program/Requirements'
    import ProgramDetails from './Program/Details'
    import ProgramPartner from './Program/Partner'

    export default {
        name: 'Programs',

        props: {
            program: {
                type: String,
                default: ''
            },
            programs: {
                type: Array,
                default: function () {
                    return []
                }
            }
        },

        components: {
            AppLayout,
            ProgramDetails,
            ProgramRequirements,
            ProgramPartner
        },

        data() {
            return {
                columns: {
                    code: true,
                    active: true,
                    name: true,
                    full_name: false,
                    partner: false,
                    domain: false,
                    strategy: true,
                    ltv: false,
                    location: false,
                    bu: false,
                    vertical: true,
                    subvertical: false,
                    level: true,
                    type: false,
                    priority: true,
                    concentrations: false,
                    accreditation: false,
                    length: false,
                    credits: false,
                    tuition: false,
                    gpa: false,
                    testing: false,
                    pod: false,
                    college: false,
                    quadrant: false,
                    edit: false,
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
                if (col == 'active' || col == 'level') {
                    return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
                } else if (col == 'ltv') {
                    return '$' + Math.round(text).toLocaleString()
                } else if (col == 'notes') {
                    return `
                        <span>
                            <svg class="ml-2 w-6 h-6 text-center text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </span>
                    `
                } else if (col == 'contacts') {
                    return `<svg class="tooltip ml-4 w-6 h-6 text-center text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
                }
                return text
            }
        },
    }
</script>
