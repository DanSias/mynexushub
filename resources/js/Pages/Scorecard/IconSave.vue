<template>
    <div>
        <svg 
            @click="toggleModal()"
            class="w-6 h-6 text-gray-400 hover:text-gray-600 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
        </svg>

        <!-- Modal Contents -->
        <div v-if="showModal" class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center mt-32 flex">
            <div class="relative w-auto my-6 mx-auto max-w-4xl">
                <!--content-->
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                <!--header-->
                <div class="flex items-start justify-between py-4 px-6 border-b border-solid border-gray-300 rounded-t">
                    <h3 class="text-xl font-semibold">
                    Save Current Scorecard
                    </h3>
                    <button class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" v-on:click="toggleModal()">
                    <span class="bg-transparent text-gray-400 hover:text-gray-700 opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none" style="transition: all .25s ease">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </span>
                    </button>
                </div>
                <!--body-->
                <div class="relative p-6 flex-auto">
                    <table class="table-auto w-full">
                        <tbody>
                            <tr>
                                <td class="border px-4 py-2">Degree Level</td>
                                <td class="border px-4 py-2 ">
                                    <span v-if="selected.level">{{ selected.level }}</span>
                                    <span v-else class="text-red-400 italic">Degree Level Required</span>
                                </td>
                            </tr>
                            <tr class="bg-gray-100">
                                <td class="border px-4 py-2">Degree CIP Code</td>
                                <td class="border px-4 py-2 ">
                                    <span v-if="selected.degree">{{ selected.degree }}</span>
                                    <span v-else class="text-red-400 italic">Degree Code Required</span>
                                </td>
                            </tr>
                            <tr class="">
                                <td class="border px-4 py-2">Occupation</td>
                                <td class="border px-4 py-2 ">
                                    <span v-if="selected.occupation">{{ selected.occupation }}</span>
                                    <span v-else class="text-red-400 italic">Occupation Required</span>
                                </td>
                            </tr>
                            <tr class="bg-gray-100">
                                <td class="border px-4 py-2">Institution</td>
                                <td class="border px-4 py-2 ">
                                    <span v-if="selected.school">{{ selected.school }}</span>
                                    <span v-else class="text-red-400 italic">Academic Institution Required</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!--footer-->
                <div class="flex items-center justify-end px-6 py-2 border-t border-solid border-gray-300 rounded-b">
                    <button class="text-gray-400 hover:text-gray-700 background-transparent font-bold uppercase px-8 py-2 text-sm outline-none focus:outline-none mr-1 mb-1" type="button" style="transition: all .25s ease" @click="toggleModal()">
                        Cancel
                    </button>
                    <button v-show="canSave" class="text-blue-500 bg-transparent border border-solid border-blue-500 hover:bg-blue-500 hover:text-white active:bg-blue-600 font-bold uppercase text-sm px-4 py-2 rounded outline-none focus:outline-none mr-1 mb-1" type="button" style="transition: all .15s ease" @click="saveScorecard()">
                        Save Scorecard 
                    </button>
                </div>
                </div>
            </div>
        </div>
        <div v-if="showModal" class="opacity-25 fixed inset-0 z-40 bg-black" @click.self="hideModal()"></div>
    </div>
</template>

<script>
export default {
    name: 'ScorecardSaveIcon',

    props: {
        selected: {
            type: Object,
            default: function () {
                return {}
            }
        }
    },

    data() {
        return {
            showModal: false
        }
    },

    computed: {
        canSave() {
            let save = this.selected
            if (save.level && save.degree && save.occupation && save.school) {
                return true
            }
            return false
        }
    },

    methods: {
        toggleModal() {
            this.showModal = ! this.showModal
        },

        saveScorecard() {
            axios
                .post('/scorecard/saves', this.selected)
                .then(({data}) => {
                    this.toggleModal()
                })
        },
        hideModal() {
            this.showModal = false
        }
    },
}
</script>

<style>

</style>