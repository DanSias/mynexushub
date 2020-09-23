<template>
    <div class="mt-8 bg-white p-4 rounded shadow container mx-auto">  
        <div class="px-4 mt-2 grid grid-cols-5 gap-4">
            <div>
                <label class="text-sm font-semibold">
                    Location
                </label>
                <LocationSelect 
                    v-model="filter.location" 
                    class="mb-4"
                    placeholder="" 
                />
            </div>
            <div>
                <label class="text-sm font-semibold">
                    Business Unit
                </label>
                <BuSelect 
                    v-model="filter.bu" 
                    class="mb-4"
                    placeholder=""
                />
            </div>
            <div>
                <label class="text-sm font-semibold">
                    Semester
                </label>
                <SemesterSelect 
                    v-model="filter.semester" 
                    :multiple="false"
                    class="mb-4"
                    placeholder=""
                />
            </div>
            <div>
                <label class="text-sm font-semibold">
                    Year
                </label>
                <YearSelect 
                    v-model="filter.year" 
                    class="mb-4"
                    placeholder=""
                />
            </div>
            <div>
                <label class="text-sm font-semibold">
                    &nbsp;
                </label>
                <button @click="setFilter()" class="w-full text-white bg-blue-500 hover:bg-blue-400 rounded py-2">
                    Go
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import GroupSelect from '../../Components/Select/Group'
import LocationSelect from '../../Components/Select/Location'
import BuSelect from '../../Components/Select/Bu'
import SemesterSelect from '../../Components/Select/Semester'
import YearSelect from '../../Components/Select/Year'

export default {
    name: 'OverviewFilter',

    components: {
        GroupSelect,
        LocationSelect,
        BuSelect,
        SemesterSelect,
        YearSelect
    },

    data() {
        return {
            filter: {
                location: [],
                bu: [],
                year: 0,
                semester: ''
            }
        }
    },


    computed: {
        currentYear() {
            let d = new Date();
            return d.getFullYear();
        }
    },

    methods: {
        setFilter() {
            this.$emit('setFilter', this.filter)
        }
    },

    // watch: {
    //     currentYear() {
    //         this.filter.year = this.currentYear
    //     }
    // },

    mounted() {
        this.filter.year = this.currentYear
    }
}
</script>

<style>

</style>