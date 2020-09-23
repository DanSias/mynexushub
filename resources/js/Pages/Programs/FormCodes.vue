<template>
    <div class="bg-white mt-8 container mx-auto rounded shadow w-full flex flex-col">
        <div class="py-3 px-6 border-b border-gray-200 text-lg text-bold flex">
            <div class="flex-grow">
                Program Code Mappings <span v-if="codes.length > 0" class="ml-2">({{ codes.length }})</span>
            </div>
            <span class="icon text-gray-300 hover:text-gray-500" aria-label="Any codes for this program that may appear in other data systems, or any roll-up codes for this program" data-balloon-pos="left" data-balloon-length="large">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </span>
        </div>

        <div v-if="codes.length == 0" class="text-center my-12 italic text-gray-400">
            No Program Code Mappings Found
        </div>

        <div class="divide-y divide-gray-300 px-8 py-8 flex-grow">
            <div v-for="(code, i) in codes" :key="i" class="py-3 text-gray-800 flex">
                <div class="flex-grow">
                    {{ code.laurus }}
                </div>
                <span class="text-gray-300 hover:text-gray-500 cursor-pointer duration-150" @click="deleteCode(code.id)" aria-label="Delete Code Map" data-balloon-pos="left">
                    <svg class="w-5 h-5 " fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </span>
            </div>
        </div>


        <div class="flex px-8 py-4 bg-gray-50 border-t border-gray-200 content-end">
            <div class="flex-grow">
                <input type="text" class="bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal" 
                    :placeholder="`Add ${program.code} Code Mapping`" 
                    v-model="input" 
                    @keyup.enter="addCode()"
                >
            </div>
            <div class="col-md-2">
                <button @click="addCode()" class="bg-blue-500 rounded py-2 px-4 text-white ml-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProgramCodesForm',

    props: {
        program: {
            type: Object,
            default: () => {}
        },
    },

    data() {
        return {
            input: '',
            codes: {}
        }
    },

    methods: {
        addMessage(message) {
            console.log(message)
        },

        getCodes() {
            axios
                .get('/data/programs/codes/' + this.program.code)
                .then(response => this.codes = response.data);
        },

        addCode() {
            axios
                .post('/codes', {
                    code: this.program.code,
                    program_id: this.program.id,
                    mosaic: this.program.code,
                    laurus: this.input
                })
                .then(response => {
                    this.addMessage('Code Has Been Saved');
                    this.getCodes();
                    this.input = '';
                });
        },

        deleteCode(id) {
            axios
                .delete('/codes/' + id)
                .then(response => {
                    this.addMessage('Code Removed');
                    this.getCodes();
                });
        }
    },

    watch: {
        program() {
            this.codes = {};
            this.getCodes();
        }
    },

    mounted() {
        this.getCodes();
    },
}
</script>
