<template>
    <div>
        <svg @click="toggleModal()" class="ml-4 w-6 h-6 text-gray-400 hover:text-gray-600 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
        <div v-if="showModal" class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center mt-32 flex">
            <div class="relative w-auto my-6 mx-auto max-w-6xl">
                <!--content-->
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                <!--header-->
                <div class="flex items-start justify-between p-5 border-b border-solid border-gray-300 rounded-t">
                    <h3 class="text-xl font-semibold">
                    Load Previous Scorecard
                    </h3>
                    <button class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" v-on:click="toggleModal()">
                    <span class="bg-transparent text-gray-400 hover:text-gray-700 opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </span>
                    </button>
                </div>
                <!--body-->
                <div class="relative p-6 flex-auto">
                    <table class="table-auto">
                        <thead>
                            <tr>
                            <th class="px-4 py-2">Level</th>
                            <th class="px-4 py-2">Degree CIP Code</th>
                            <th class="px-4 py-2">Occupation</th>
                            <th class="px-4 py-2">Institution</th>
                            <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(save, i) in saves" :key="i" class="hover:bg-yellow-50" :class="(i % 2 == 0) ? 'bg-gray-50' : ''">
                                <td class="border px-4 py-2">{{ save.level }}</td>
                                <td class="border px-4 py-2">
                                    <span v-if="JSON.parse(save.cip6d).label">{{ JSON.parse(save.cip6d).label }}</span>
                                    <span v-else>{{ JSON.parse(save.cip6d).value }}</span>
                                </td>
                                <td class="border px-4 py-2">{{ save.occupation }}</td>
                                <td class="border px-4 py-2">{{ save.school }}</td>
                                <td class="border pr-4">
                                    <svg @click="loadSave(save)" class="ml-4 w-4 h-4 text-gray-400 hover:text-gray-600 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!--footer-->
                <div class="flex items-center justify-end px-6 py-2 border-t border-solid border-gray-300 rounded-b">
                    <button class="text-gray-500 hover:text-gray-700 background-transparent font-bold uppercase px-6 py-2 text-sm outline-none focus:outline-none mr-1 mb-1" type="button" style="transition: all .15s ease" v-on:click="toggleModal()">
                    Close
                    </button>
                </div>
                </div>
            </div>
        </div>
        <div v-if="showModal" class="opacity-25 fixed inset-0 z-40 bg-black"></div>
    </div>
</template>

<script>
export default {
    name: 'ScorecardLoadIcon',

    data() {
        return {
            showModal: false,
            saves: []
        }
    },

    methods: {
        toggleModal() {
            this.showModal = ! this.showModal
        },
        getSaves() {
            axios
                .get('/scorecard/saves')
                .then(({data}) => this.saves = data)
        },
        loadSave(save) {
            console.log(save)
        }
    },

    watch: {
        showModal() {
            if (this.showModal) {
                this.getSaves()
            }
        }
    }
}
</script>

<style>

</style>