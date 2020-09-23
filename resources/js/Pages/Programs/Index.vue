<template>
    <div>
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
                    <span class="capitalize">{{ name.replace('_', ' ') }}</span>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div v-if="showFilter" class="container mx-auto mt-8 bg-white rounded shadow">
            <p class="text-gray-800 text-semibold text-lg py-4 px-6 border-b border-gray-200 flex">
                <span class="flex-grow">Filter Programs</span>
                <span class="text-gray-400 hover:text-gray-700 cursor-pointer" @click="showFilter = false">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </span>
            </p>
            <div class="grid gap-5 grid-cols-6 p-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Search Program Code
                    </label>
                    <input 
                        v-model="filter.search"
                        id="search"
                        class="bg-white  focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal" 
                    >
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Location
                    </label>
                    <LocationFilter 
                        v-model="filter.location" 
                        placeholder=""
                    />
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Business Unit
                    </label>
                    <BuFilter 
                        v-model="filter.bu" 
                        placeholder=""
                    />
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Academic Partner
                    </label>
                    <PartnerFilter 
                        v-model="filter.partner" 
                        placeholder=""
                    />
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Strategy
                    </label>
                    <StrategyFilter 
                        v-model="filter.strategy" 
                        placeholder=""
                    />
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Active Status
                    </label>
                    <ActiveFilter 
                        v-model="filter.active" 
                        placeholder=""
                    />
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
                        v-for="(program, j) in filteredPrograms" :key="j" 
                        class="hover:bg-yellow-50"
                        :class="(j % 2 == 0) ? 'bg-gray-50' : ''"
                    >
                        <td 
                            v-for="(col, i) in visibleColumns" :key="i" 
                            class="border px-4 py-2"
                        >
                            <span v-if="col == 'code'">
                                <inertia-link :href="'/programs/' + program.code" class="hover:text-blue-500 hover:underline hover:font-semibold">{{ program.code }}</inertia-link></span>
                            <span v-else v-html="formatCell(program[col], col)"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import LocationFilter from '../../Components/Select/Location'
import BuFilter from '../../Components/Select/Bu'
import PartnerFilter from '../../Components/Select/Partner'
import StrategyFilter from '../../Components/Select/Strategy'
import ActiveFilter from '../../Components/Select/Active'

export default {
    name: 'ProgramsTable',

    props: {
        showColumns: {
            type: Boolean,
            default: false,
        },
        showFilter: {
            type: Boolean,
            default: false,
        },
        programs: {
            type: [Array],
            default: () => {}
        },
        urlFilter: {
            type: Object,
            default: () => {}
        }
    },

    components: {
        LocationFilter,
        BuFilter,
        PartnerFilter,
        StrategyFilter,
        ActiveFilter,
    },

    data() {
        return {
            filter: {
                search: '',
                active: [],
                location: [],
                bu: [],
                partner: [],
                strategy: [],
                active: ['TRUE']
            },
            columns: {
                code: true,
                active: false,
                name: true,
                full_name: false,
                partner: false,
                domain: false,
                strategy: true,
                ltv: false,
                location: true,
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
        },
        filteredPrograms() {
            let programs = this.programs;
            if (! _.isEmpty(this.filter.location)) {
                programs = _.filter(programs, p => {
                    return this.filter.location.includes(p.location);
                });
            }
            if (! _.isEmpty(this.filter.bu)) {
                programs = _.filter(programs, p => {
                    return this.filter.bu.includes(p.bu);
                });
            }
            if (! _.isEmpty(this.filter.partner)) {
                programs = _.filter(programs, p => {
                    return this.filterPartnerIds.includes(p.partner_id);
                });
            }
            if (! _.isEmpty(this.filter.vertical)) {
                programs = _.filter(programs, p => {
                    return this.filter.vertical.includes(p.vertical);
                });
            }
            if (! _.isEmpty(this.filter.strategy)) {
                programs = _.filter(programs, p => {
                    return this.filter.strategy.includes(p.strategy);
                });
            }
            if (! _.isEmpty(this.filter.active)) {
                programs = _.filter(programs, p => {
                    return this.filter.active.includes(p.active);
                });
            }
            
            if (this.filter.search) {
                programs = _.filter(programs, p => {
                    return p.code.toLowerCase().includes(this.filter.search.toLowerCase());
                });
            }
            return programs;
        },
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
            } else if (col == 'priority') {
                if (text) {
                    return `<svg class="w-6 h-6 w-full text-center text-yellow-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>`
                }
                return ``
            } else if (col == 'notes') {
                return `
                    <span>
                        <svg class="ml-2 w-6 h-6 text-center text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </span>
                `
            } else if (col == 'contacts') {
                return `<svg class="tooltip ml-4 w-6 h-6 text-center text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
            } else if (col == 'strategy') {
                switch (text) {
                    case 'Invest':
                        return `<span class="text-green-500 font-semibold">${text}</span>` 
                        break;
                    case 'Optimize':
                        return `<span class="text-blue-500 font-semibold">${text}</span>` 
                        break;
                    case 'Divest':
                        return `<span class="text-orange-500 font-semibold">${text}</span>` 
                        break;
                    case 'Cut':
                        return `<span class="text-red-500 font-semibold">${text}</span>` 
                        break;
                
                    default:
                        return text
                        break;
                }
            } 
            return text
        }
    },

    mounted() {
        // if (this.urlFilter.partner) {
        //     this.filter.partner = this.urlFilter.partner
        // }
    }
}
</script>

<style>

</style>