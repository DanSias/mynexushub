<template>
    <div class="bg-white mt-8 container mx-auto rounded shadow w-full flex flex-col">
        <div class="py-3 px-6 border-b border-gray-200 text-lg text-bold">
            Program Tracks <span v-if="tracks.length > 0" class="ml-2">({{ tracks.length }})</span>
        </div>

        <div v-if="tracks.length == 0" class="text-center my-12 italic text-gray-400">
            No Program Tracks
        </div>

        <div class="divide-y divide-gray-300 px-8 py-8 flex-grow">
            <div v-for="(track, i) in tracks" :key="i" class="py-3 text-gray-800 flex">
                <div class="flex-grow">
                    {{ track.track }}
                </div>
                <span class="text-gray-300 hover:text-gray-500 cursor-pointer duration-150" @click="deleteTrack(track.id)" aria-label="Delete Track" data-balloon-pos="left">
                    <svg class="w-5 h-5 " fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </span>
            </div>
        </div>

        <div class="flex px-8 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex-grow">
                <input type="text" class="bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal" 
                    :placeholder="`Add ${program.code} Track`" 
                    v-model="input" 
                    @keyup.enter="addTrack()"
                >
            </div>
            <div class="">
                <button @click="addTrack()" class="bg-blue-500 rounded py-2 px-4 text-white ml-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProgramTracksForm',

    props: {
        program: {
            type: Object,
            default: function () {
                return {};
            }
        },
    },

    data() {
        return {
            input: '',
            tracks: {}
        }
    },

    methods: {
        addMessage(message) {
            console.log(message)
        },

        getTracks() {
            axios
                .get('/data/programs/tracks/' + this.program.code)
                .then(response => this.tracks = response.data);
        },

        addTrack() {
            axios
                .post('/tracks', {
                    program: this.program.code,
                    program_id: this.program.id,
                    track: this.input
                })
                .then(response => {
                    this.addMessage('Track Has Been Saved');
                    this.getTracks();
                    this.input = '';
                });
        },

        deleteTrack(id) {
            axios
                .delete('/tracks/' + id )
                .then(response => {
                    this.addMessage('Track Removed');
                    this.getTracks();
                });
        }
    },

    watch: {
        program() {
            this.tracks = {};
            this.getTracks();
        }
    },

    mounted() {
        this.getTracks();
    },
}
</script>
