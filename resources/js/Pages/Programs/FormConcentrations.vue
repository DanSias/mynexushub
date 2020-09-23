<template>
    <div class="bg-white mt-8 container mx-auto rounded shadow w-full flex flex-col">
        <div class="py-3 px-6 border-b border-gray-200 text-lg text-bold">
            Program Concentrations <span v-if="concentrations.length > 0" class="ml-2">({{ concentrations.length }})</span>
        </div>

        <div v-if="concentrations.length == 0" class="text-center my-12 italic text-gray-400">
            No Program Concentrations
        </div>

        <div class="divide-y divide-gray-300 px-8 py-8 flex-grow">
            <div v-for="(concentration, i) in concentrations" :key="i" class="py-3 text-gray-800 flex">
                <div class="flex-grow">
                    {{ concentration.concentration }}
                </div>
                <span class="text-gray-300 hover:text-gray-500 cursor-pointer duration-150" @click="deleteConcentration(concentration.id)" aria-label="Delete concentration" data-balloon-pos="left">
                    <svg class="w-5 h-5 " fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </span>
            </div>
        </div>


        <div class="flex px-8 py-4 bg-gray-50 border-t border-gray-200 content-end">
            <div class="flex-grow">
                <input type="text" class="bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal" 
                    :placeholder="`Add ${program.code} Concentration`" 
                    v-model="input" 
                    @keyup.enter="addConcentration()"
                >
            </div>
            <div class="col-md-2">
                <button @click="addConcentration()" class="bg-blue-500 rounded py-2 px-4 text-white ml-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProgramConcentrationsForm',

    props: {
        program: {
            type: Object,
            default: () => {}
        },
    },

    data() {
        return {
            input: '',
            concentrations: {}
        }
    },

    methods: {
        addMessage(message) {
            console.log(message)
        },

        getConcentrations() {
            axios
                .get('/data/programs/concentrations/' + this.program.code)
                .then(response => this.concentrations = response.data);
        },

        addConcentration() {
            axios
                .post('/concentrations', {
                    program: this.program.code,
                    program_id: this.program.id,
                    concentration: this.input
                })
                .then(response => {
                    this.addMessage('Concentration Has Been Saved');
                    this.getConcentrations();
                    this.input = '';
                });
        },

        deleteConcentration(id) {
            axios
                .delete('/concentrations/' + id)
                .then(response => {
                    this.addMessage('Concentration Removed');
                    this.getConcentrations();
                });
        }
    },

    watch: {
        program() {
            this.concentrations = {};
            this.getConcentrations();
        }
    },

    mounted() {
        this.getConcentrations();
    },
}
</script>
