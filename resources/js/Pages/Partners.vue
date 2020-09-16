<template>
    <app-layout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <span v-if="partner">{{ partner }} Partner Details</span>
                    <span v-else>Academic Partners</span>
                </h2>
                <div class="flex">
                    <span aria-label="Add New Partner" data-balloon-pos="down" class="text-gray-500 hover:text-blue-500">
                        <svg class="w-6 h-6 " fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                </div>
            </div>
        </template>

        <div class="container mx-auto mt-8 p-6 bg-white rounded shadow">
            <div class="grid gap-4 grid-cols-8">
                <div v-for="(value, name, i) in columns" :key="i">
                    <input type="checkbox" v-model="columns[name]"> 
                    <span class="capitalize">{{ name.replace('_', ' ') }}</span>
                </div>
            </div>
        </div>

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
                        v-for="(partner, j) in partners" :key="j" 
                        class="hover:bg-yellow-50"
                        :class="(j % 2 == 0) ? 'bg-gray-50' : ''"
                    >
                        <td 
                            v-for="(col, i) in visibleColumns" :key="i" 
                            class="border px-4 py-2"
                        >
                            <span v-html="formatCell(partner[col], col)"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
            This thing for {{ partners }}
        </div>

    </app-layout>
</template>

<script>
    import AppLayout from './../Layouts/AppLayout'

    export default {
        name: 'Partners',

        props: {
            partner: {
                type: String,
                default: ''
            },
            partners: {
                type: Array,
                default: function () {
                    return []
                }
            }
        },

        components: {
            AppLayout,
        },

        data() {
            return {
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
                }
                return text
            }
        },
    }
</script>
