<template>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label>Start</label>
            <RangeStart
                class="w-full"
                :range="range" 
                @setRangeStart="setRangeStart"
            /> 
        </div>
        
        <div>
            <label>End</label>
            <RangeEnd 
                class="w-full"
                :range="range"
                @setRangeEnd="setRangeEnd"
            />
        </div>
    </div>
</template>

<script>
import RangeStart from './RangeStart'
import RangeEnd from './RangeEnd'
import DateMixins from '../../Mixins/Dates'

export default {
    name: 'DateRange',

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
        RangeStart,
        RangeEnd
    },

    watch: {
        rangeArray() {
            this.$emit('rangeArray', this.rangeArray)
            console.log('new range ' + this.rangeArray)
        }
    },
}
</script>

<style>

</style>