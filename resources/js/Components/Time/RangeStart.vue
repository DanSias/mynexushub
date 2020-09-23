<template>
    <div>
        <Datepicker input-class="border border-gray-200 px-4 py-2 text-center rounded focus:border-blue-500 focus:outline-none"
            calendar-class=""
            placeholder="Start Date" 
            v-model="startDate"
            :minimumView="'month'" 
            :maximumView="'month'" 
            format="MMMM yyyy"  >
        </Datepicker>
    </div>
</template>


<script>
import Datepicker from "vuejs-datepicker";

export default {
    name: 'DateRangeStart',

    props: {
        range: {
            type: Object,
            default: () => {}
        }
    },

    components: {
        Datepicker,
    },

    data() {
        return {
            rangeStart: ''
        }
    },

    computed: {

        startDate: {
            get() {
                let date = String(this.rangeStart);
                let yr = date.substring(0,4);
                let mo = date.substring(4,6) - 1;
                let startDateTime = new Date(yr, mo);
                return startDateTime;
            },
            set(value) {
                let date = new Date(value);
                let mo = date.getMonth() + 1;
                mo = this.pad(mo, 2);
                let yr = date.getFullYear();
                let string = String(yr) + String(mo);
                this.setRangeStart(string)
            }
        }
    },

    methods: {
        setRangeStart(string) {
            this.$emit('setRangeStart', string)
        },

        pad(a, b) {
            return(1e15 + a + "").slice(-b);
        },
    },

    mounted() {
        if (this.range.start) {
            this.rangeStart = this.range.start
        }
    }
}
</script>

<style>

.datepicker-input {
    cursor: pointer !important;
    background-color: transparent !important;
}
</style>
