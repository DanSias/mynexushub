<template>
    <div class="bg-white mt-8 p-8 container mx-auto rounded shadow lg:w-1/2 md:w-full">
        <table class="table-fixed w-full">
            <tbody>
                <tr v-for="(key, index) in formRows" :key="index">
                    <td :class="tdClass">
                        <label :class="labelClass">
                            <span v-if="capitalize[key]">{{ capitalize[key] }}</span>
                            <span v-else>{{ formatKey(key) }}</span>
                        </label>
                    </td>
                    <td :class="tdClass">
                        <span v-if="selectInputs.includes(key)">
                            <div class="grid grid-cols-3 text-center">
                                <div>
                                    <input type="radio" class="uk-radio uk-margin-small-left" :id="key + 'yes'" value="yes" v-model="form[key]">
                                    <label :for="key + 'yes'" class="clickable">Yes</label>
                                </div>
                                <div>
                                    <input type="radio" class="uk-radio uk-margin-large-left" :id="key + 'no'" value="no" v-model="form[key]">
                                    <label :for="key + 'no'" class="clickable">No</label>
                                </div>
                                <div>
                                    <input type="radio" class="uk-radio uk-margin-large-left" :id="key + 'na'" value="n/a" v-model="form[key]">
                                    <label :for="key + 'na'" class="clickable">N/A</label>
                                </div>
                            </div>
                        </span>
                        <!-- Textarea for notes -->
                        <span v-else-if="key == 'program_notes'">
                            <textarea name="" id=""  rows="3" class="uk-textarea" v-model="form[key]"></textarea>
                        </span>
                        <!-- Min and Max inputs for credit hours and program length -->
                        <span v-else-if="['credit_hours', 'program_months'].includes(key)" class="flex">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <input :class="inputClass" :type="inputType(key)" v-model="form[key]" placeholder="Base">
                                </div>
                                <div>
                                    <input :class="inputClass" :type="inputType(key)" v-model="form[key + '_max']" placeholder="Max">
                                </div>
                            </div>
                        </span>
                        <!-- In State / Out State inputs on the same line-->
                        <span v-else-if="['tuition_in_state', 'tuition_credit_hour'].includes(key)">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <input :class="inputClass" :type="inputType(key)" v-model="form[key]" placeholder="In State">
                                </div>
                                <div>
                                    <input v-if="key == 'tuition_credit_hour'" :class="inputClass" :type="inputType(key)" v-model="form[key + '_os']" placeholder="Out of State">
                                    <input v-else :class="inputClass" :type="inputType(key)" v-model="form['tuition_out_state']" placeholder="Out of State">
                                </div>
                            </div>
                        </span>
                        <!-- Text Input Field -->
                        <span v-else>
                            <input :class="inputClass" :type="inputType(key)" v-model="form[key]">
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <button 
            class="bg-white border-2 text-blue-500 border-blue-500 hover:bg-blue-500 hover:text-white font-bold py-3 px-4 mt-6 rounded w-full transition duration-200" 
            @click.prevent="submitForm()">Update {{ form.code }}
        </button>
    </div>
</template>

<script>
export default {
    name: 'ProgramRequirementsForm',

    props: {
        program: {
            type: Object,
            default: () => {},
        },
        action: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            form: {
                id: '',
                program_id: '',
                code: '',
                credit_hours: '',
                credit_hours_max: '',
                program_months: '',
                program_months_max: '',
                tuition_credit_hour: '',
                tuition_credit_hour_os: '',
                tuition_in_state: '',
                tuition_out_state: '',
                fees: '',
                tuition_notes: '',
                gpa_required: '',
                gpa_notes: '',
                testing_required: '',
                gmat: '',
                gmat_waiver: '',
                gre: '',
                gre_waiver: '',
                testing_notes: '',
                experience_needed: '',
                transfer_credits_accepted: '',
                transfer_credits_details: '',
                program_notes: '',
            },
            selectInputs: [
                'testing_required',
                'gmat',
                'gmat_waiver', 
                'gre',
                'gre_waiver',
                'transfer_credits_accepted'
            ],
            capitalize: {
                gpa_required: 'GPA Required',
                gpa_notes: 'GPA Notes',
                gmat: 'GMAT',
                gmat_waiver: 'GMAT Waiver',
                gre: 'GRE',
                gre_waiver: 'GRE Waiver',
            },
            tdClass: 'border px-4 py-4',
            labelClass: 'capitalize text-gray-800 text-lg',
            inputClass: 'bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal',
        }
    },

    computed: {
        requirements() {
            return this.program.requirements;
        },
        formRows() {
            let rows = []
            let skip = ['id', 'program_id', 'code', 'credit_hours_max', 'program_months_max', 'tuition_credit_hour_os', 'tuition_out_state']
            _.forEach(this.form, (value, key) => {
                if (! skip.includes(key)) {
                    rows.push(key)
                }
            }) 
            return rows
        }
    },

    methods: {
        setForm() {
            if (! this.program || Object.keys(this.program).length === 0) {
                this.clearForm();
                this.form.code = this.program.code;
                this.form.program_id = this.program.id;
            } else {
                _.forEach(this.form, (field, key) => {
                    if (this.requirements && this.requirements[key]) {
                        this.form[key] = this.requirements[key];
                    } else {
                        this.form[key] = '';
                    }
                });
                this.form.code = this.program.code;
                this.form.program_id = this.program.id;
                // this.form = this.program;
            }
        },

        formatKey(key) {
            if (key == 'tuition_credit_hour_os') {
                return 'Tuition Credit Hour OS';
            } else if (key == 'program_months') {
                return 'Program Length (Months)';
            } else if (key == 'tuition_in_state') {
                return 'Total Tuition';
            }
            if (key.includes('_')) {
                let formatted = key.replace(/_/g, ' ');
                return formatted;
            } else {
                return key;
            }
        },

        clearForm() {
            // _.forEach(this.form, (pro, key) => {
            //     this.form[key] = '';
            // });
        },

        inputType(key) {
            let numbers = [
                'credit_hours',
                'program_months',
                'tuition_in_state', 
                'tuition_out_state',
                'gpa_required'
            ]
            if (numbers.includes(key)) {
                return 'number';
            }
            return 'text';
        },

        submitForm() {
            if (this.form.id) {
                axios
                    .patch('/api/program-requirements/' + this.form.id, this.form)
                    .then(response => {
                        console.log(response.data);
                        if (response.data.id) {
                            _.forEach(this.form, (pro, key) => {
                                this.form[key] = response.data[key];
                            });
                            this.addMessage('Updates Saved!');
                            this.getPrograms();
                            this.$emit('close');
                        }
                    });
            } else {
                this.form.code = this.program.code;
                this.form.program_id = this.program.id;
                axios
                    .post('/api/program-requirements', this.form)
                    .then(response => {
                        console.log(response.data);
                        if (response.data.id) {
                            _.forEach(this.form, (pro, key) => {
                                this.form[key] = response.data[key];
                            });
                            this.addMessage('Updates Saved!');
                            this.getPrograms();
                            this.$emit('close');
                        }
                    })
            }
        },
    },

    watch: {
        requirements() {
            this.setForm();
        },
        program() {
            this.setForm();
        }
    },

    mounted() {
        this.setForm();
    }
}
</script>


<style scoped>
label {
    text-transform:capitalize;
}
tr:nth-child(even) {
    background-color: #f9fafb;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    /* display: none; <- Crashes Chrome on hover */
    -webkit-appearance: none;
    margin: 0; /* <-- Apparently some margin are still there even though it's hidden */
}

input[type=number] {
    -moz-appearance:textfield; /* Firefox */
}
</style>
