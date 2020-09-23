<template>
    <div>
        <label>Twelve Months Ending</label>
        <multiselect class="search-box" 
            :value="month"
            @input="setRange"
            :options="monthList" 
            :allow-empty="false"
            :custom-label="yearMonthToText"
            placeholder="Ending Month" 
            selectLabel=">" 
            deselectLabel="x">
        </multiselect>
    </div>
</template>


<script>
import Multiselect from 'vue-multiselect'
import DateMixins from '../../Mixins/Dates'

export default {
    name: 'TwelveMonths',

    mixins: [
        DateMixins
    ],

    props: {
        range: {
            type: Object,
            default: () => {}
        }
    },

    components: {
        Multiselect
    },
    
    data() {
        return {
            month: '',
        }
    },

    computed: {
        monthList() {
            let list = this.monthsAgo;
            return list;
        },
        rangeLength() {
            return this.rangeArray.length;
        },
    },

    methods: {
        ucFirst(month) {
            return month + 'word';
        },
        setRange(end) {
            let start = this.twelveMonthsAgo(end)
            this.month = String(end)

            this.$emit('rangeEnd', this.month)
            this.$emit('rangeStart', start)
        }
    },

    mounted() {
        if (this.rangeLength == 12) {
            this.month = this.range.end;
        }
    },

    watch: {
        rangeArray() {
            if (this.rangeLength == 12) {
                this.month = this.range.end;
                console.log('the range is a year ending ' + this.range.end);
            } else {
                this.month = '';
            }
        }
    }
}
</script>
