<template>
    <div class="container mx-auto mt-8 bg-gray-200">
        <h2 class="text-2xl text-gray-800">Welcome to test town!</h2>
        <TimeTabs
           @range="setRange"
        />
    </div>
</template>

<script>
import TimeTabs from '../Components/Time/Tabs'

export default {
    name: 'NexusTest',

    components: {
        TimeTabs
    },

    methods: {
        setRange(range) {
            console.log('testing the range for ' + range)
        }
    }
}
</script>

<style>

</style>