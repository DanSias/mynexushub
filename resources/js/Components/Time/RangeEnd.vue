<template>
    <div>
        <Datepicker input-class="border border-gray-200 px-4 py-2 text-center rounded focus:border-blue-500 focus:outline-none"
            calendar-class=""
            placeholder="End Date" 
            v-model="endDate"
            :minimumView="'month'" 
            :maximumView="'month'" 
            format="MMMM yyyy"  >
        </Datepicker>
    </div>
</template>


<script>
import Datepicker from "vuejs-datepicker";

export default {
    name: 'DateRangeEnd',

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
            rangeEnd: ''
        }
    },

    computed: {

        endDate: {
            get() {
                let date = String(this.rangeEnd);
                let yr = date.substring(0,4);
                let mo = date.substring(4,6) - 1;
                let endDateTime = new Date(yr, mo);
                return endDateTime;
            },
            set(value) {
                let date = new Date(value);
                let mo = date.getMonth() + 1;
                mo = this.pad(mo, 2);
                let yr = date.getFullYear();
                let string = String(yr) + String(mo);
                this.setRangeEnd(string)
            }
        }
    },

    methods: {
        setRangeEnd(string) {
            this.$emit('setRangeEnd', string)
        },

        pad(a, b) {
            return(1e15 + a + "").slice(-b);
        },
    },

    mounted() {
        if (this.range.end) {
            this.rangeEnd = this.range.end
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
