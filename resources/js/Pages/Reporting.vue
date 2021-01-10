<template>
    <app-layout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight capitalize">
                    Reporting Headline for {{ reportType }}
                </h2>
                <div class="flex">
                    Right Side
                </div>
            </div>
        </template>


        <!-- Score Details -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-3 gap-4">
                Stuff
            </div>
        </div>

    </app-layout>
</template>

<script>
import AppLayout from './../Layouts/AppLayout'


export default {
    name: 'ReportingHome',

    props: {
        reportType: {
            type: String,
            default: ''
        }
    },

    components: {
        AppLayout
    },

    data() {
        return {
            ready: false
        }
    },

    methods: {

    },
}
</script>
