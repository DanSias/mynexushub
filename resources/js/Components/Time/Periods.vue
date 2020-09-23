<template>
    <div>
        <div class="grid grid-cols-6 gap-3">
            <button 
                v-for="(period, i) in periods" :key="i"
                class="w-full text-semibold border rounded border-blue-200 capitalize py-2 duration-200 outline-none focus:outline-none" 
                :class="(period == rangeType) ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-blue-50 hover:border-blue-500'" 
                @click="setRange(period)"
            >
                <span v-if="headings[period]">
                    <span v-html="headings[period]"></span>
                </span>
                <span v-else>
                    {{ period }}
                </span>
            </button>
        </div>
    </div>
</template>

<script>
import DateMixins from '../../Mixins/Dates'

export default {
    name: 'TimePeriodButtons',

    props: {
        range: {
            type: Object,
            default: () => {}
        }
    },

    mixins: [
        DateMixins
    ],

    computed: {
        periods() {
            if (['january', 'february'].includes(this.calendarMonth)) {
                return [
                    'lastYear',
                    'lastTwelve',
                    // 'yearComplete',
                    'lastMonth',
                    'thisMonth',
                    'fullYear'
                ];
            } else {
                return [
                    'lastYear',
                    'lastTwelve',
                    'yearComplete',
                    'lastMonth',
                    'thisMonth',
                    'fullYear'
                ];
            }
        },

        lastCalendarYear() {
            return this.calendarYear - 1;
        },

        headings() {
            let lastMonthYear = 
                (this.lastCompleteMonth == 'december') 
                ? this.calendarYear - 1 
                : this.calendarYear
            
            let headings = {
                lastMonth: `${this.lastCompleteMonth}<br>${lastMonthYear}`,
                thisMonth: `${this.calendarMonth}<br>${this.calendarYear}`,
                fullYear: `Full Year<br>${this.calendarYear}`,
                // yearComplete: `Complete ${this.calendarYear}`,
                yearComplete: `Jan - ${this.lastCompleteMonth.substring(0,3)}<br>${this.calendarYear}`,
                lastTwelve: `Last 12<br>Complete`,
                lastYear: `Full Year<br>${this.calendarYear - 1}`
            };

            return headings;
        },

        rangeType() {
            if (this.range === undefined) {
                return ''
            }
            if (! this.range.start || ! this.range.end) {
                return ''
            }
            if (this.range.start == this.range.end  && this.range.end == this.calendarYearMonth) {
                return 'thisMonth';
            } else if (this.range.start == this.range.end  && this.range.end == this.lastCalendarYearMonth) {
                return 'lastMonth';
            } else if (this.range.start == this.calendarYear + '01'  && this.range.end == this.lastCalendarYearMonth) {
                return 'yearComplete';
            } else if (this.range.start == this.calendarYear + '01'  && this.range.end == this.calendarYear + '12') {
                return 'fullYear';
            }else if (this.range.start == this.lastCalendarYear + '01'  && this.range.end == this.lastCalendarYear + '12') {
                return 'lastYear';
            }else if (this.range.start == String(parseInt(this.lastCalendarYearMonth - 99))  && this.range.end == this.lastCalendarYearMonth) {
                return 'lastTwelve';
            }
        }
    },

    methods: {
        setRange(period) {
            switch (period) {
                case 'thisMonth':
                    this.$emit('rangeStart', this.calendarYearMonth);
                    this.$emit('rangeEnd', this.calendarYearMonth);
                    break;
                case 'lastMonth':
                    this.$emit('rangeStart', this.lastCalendarYearMonth);
                    this.$emit('rangeEnd', this.lastCalendarYearMonth);
                    break;
                case 'yearComplete':
                    this.$emit('rangeStart', this.calendarYear + '01');
                    this.$emit('rangeEnd', this.lastCalendarYearMonth);
                    break;
                case 'fullYear':
                    this.$emit('rangeStart', this.calendarYear + '01');
                    this.$emit('rangeEnd', this.calendarYear + '12');
                    break;
                case 'lastYear':
                    this.$emit('rangeStart', this.lastCalendarYear + '01');
                    this.$emit('rangeEnd', this.lastCalendarYear + '12');
                    break;
                case 'lastTwelve':
                    if (this.calendarMonth == 'january') {
                        let startHere = String((this.calendarYear - 1) + '01');
                        this.$emit('rangeStart', startHere);
                    } else {
                        this.$emit('rangeStart', String(parseInt(this.lastCalendarYearMonth - 99)));
                    }
                    this.$emit('rangeEnd', this.lastCalendarYearMonth);
                    break;
            
                default:
                    break;
            }
        }
    }
}
</script>

<style scoped>
.uk-button {
    line-height: 1.4rem;
    padding-top: 6px;
    padding-bottom: 6px;
}
</style>