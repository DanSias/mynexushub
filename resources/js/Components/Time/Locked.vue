<template>
    <div>
        <div class="flex justify-between">
            <div class="w-1/4">
                <strong>Number of Months</strong>
                <multiselect 
                    class="w-full"
                    v-model="span" 
                    :options="spans" 
                    :searchable="false" 
                    :close-on-select="true" 
                    :show-labels="false" 
                    placeholder="Months">
                </multiselect>
            </div>
            <div class="w-1/4">
                <strong>Days Locked</strong>
                <multiselect 
                    class="w-full"
                    v-model="locked" 
                    placeholder="" 
                    :options="maturity" 
                    :show-labels="false" 
                    :searchable="false" 
                    :allow-empty="false">
                </multiselect>
            </div>
            <div v-if="start && end" class="uk-text-truncate">
                <br>
                <h5 class="mt-1 text-xl">{{ yearMonthToText(start) }} <span v-show="start != end">- {{ yearMonthToText(end) }}</span></h5>
            </div>
            <div>
                <br>
                <button class="w-full px-4 py-2 bg-blue-500 text-white rounded" :class="buttonClass" @click="setRange()">Set Range</button>
            </div>
        </div>
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import DateMixins from '../../Mixins/Dates'

export default {
    name: 'RangeLocked',

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
            dates: [],
            span: 12,
            locked: 0,
            spans: [
                1,
                3,
                6,
                12,
                18
            ],
            maturity: [
                15,
                30,
                60,
                90,
                120,
                180
            ]
        }
    },

    computed: {
        mature() {
            return this.dates.mature;
        },
        end() {
            return (this.mature && this.mature[this.locked]) ? this.mature[this.locked] : this.calendarYearMonth;
        },
        start() {
            let start = this.end
            if (! start) {
                return null;
            }
            for (let i = 0; i < this.span - 1; i++) {
                start = this.subtractMonth(start);
            }
            return start;
        },
        monthBeforeEnd() {
            return this.subtractMonth(this.end);
        },
        buttonClass() {
            if (this.range.start == this.start && this.range.end == this.end) {
                return 'uk-button-default';
            }
            return 'uk-button-primary';
        }
    },

    methods: {
        getDates() {
            axios
                .get('/data/dates')
                .then(({data}) => this.dates = data)
        },
        setRangeStart(value) {
            this.range.start = value
            console.log('new start ' + value)
        },
        setRangeEnd(value) {
            this.range.end = value
            console.log('new end ' + value)
        },
        setRange() {
            this.setRangeStart(this.start);
            this.setRangeEnd(this.end);
        },
        subtractMonth(yearMonth) {
            let mo = yearMonth.slice(-2);
            let yr = yearMonth.slice(0, 4);
            if (mo == '01') {
                let lastYear = parseInt(yr) - 1;
                return lastYear + '12';
            } else {
                let base = parseInt(yearMonth);
                let newMonth = base - 1;
                return String(newMonth);
            }
        },
    },

    mounted() {
        this.getDates()
    }
}
</script>

<style scoped>
h5 {
    font-size: 18px;
}
</style>