<template>
    <div class="bg-white py-4 rounded">
        <ul class="flex border-b border-gray-200 font-semibold duration-200">
            <li 
                v-for="(tab, i) in tabs" :key="i" 
                class="mr-3" 
                :class="(tab == active) ? 'border-blue-500 text-blue-500 border-b-2' : 'text-gray-700'"
            >
                <a class="inline-block py-2 px-6" href="#" @click.prevent="setActive(tab)">
                    {{ headings[tab] }}
                </a>
            </li>
        </ul>
        <div class="px-3">
            <div v-if="active == 'periods'">
                <TimePeriods 
                    :range="range"
                    @rangeStart="rangeStart"
                    @rangeEnd="rangeEnd"
                    class="mt-4"
                />
            </div>
            <div v-if="active == 'twelve'">
                <TwelveMonths 
                    :range="range"
                    @rangeStart="rangeStart"
                    @rangeEnd="rangeEnd"
                    class="mt-4"
                />
            </div>
            <div v-if="active == 'range'">
                <DateRange 
                    :range="range"
                    class="mt-4"
                />
            </div>
            <div v-if="active == 'locked'">
                <LockedRange 
                    :range="range"
                    class="mt-4"
                />
            </div>
        </div>
    </div>
</template>

<script>
import TimePeriods from './Periods'
import DateRange from './Range'
import LockedRange from './Locked'
import TwelveMonths from './TwelveMonths'
import DateMixins from '../../Mixins/Dates'

export default {
    name: 'TimeSelectTabs',

    mixins: [DateMixins],

    components: {
        TimePeriods,
        DateRange,
        LockedRange,
        TwelveMonths,
    },

    data() {
        return {
            range: {
                start: '',
                end: ''
            },
            active: 'periods',
            tabs: [
                'periods',
                'twelve',
                'range',
                'locked'
            ],
            headings: {
                periods: 'Quick Date Select',
                twelve: 'Twelve Month Span',
                range: 'Custom Date Range',
                locked: 'Locked Month Range'
            }
        }
    },

    methods: {
        setActive(item) {
            this.active = item
        },
        rangeStart(text) {
            this.range.start = text
        },
        rangeEnd(text) {
            this.range.end = text
        }
    },

    watch: {
        rangeArray() {
            console.log('tabs saw the range change')
            this.$emit('rangeArray', this.rangeArray)
        }
    },

    mounted() {
        this.range.start = this.currentYearMonth
        this.range.end = this.currentYearMonth
    }
}
</script>
