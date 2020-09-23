<template>
    <div class="">
        <span v-for="(row, j) in rows" :key="j">
            <span v-if="value(row)">
                <p class="capitalize text-gray-500">{{ heading(row) }}</p>
                <p class="text-gray-900 text-xl mb-4">
                    <!-- Value of row logic -->
                    <span class="" v-html="value(row)"></span>
                    <span v-if="row == 'tuition' && requirements && requirements.tuition_notes">
                        - 
                        <span v-html="requirements.tuition_notes.replace(/(?:\r\n|\r|\n)/g, '<br>')"></span>

                    </span>
                    <span v-if="row == 'gpa' && requirements && requirements.gpa_notes">
                        - 
                        <span v-html="requirements.gpa_notes.replace(/(?:\r\n|\r|\n)/g, '<br>')"></span>

                    </span>
                    <span v-if="row == 'transfer' && value(row) == 'Yes' && requirements && requirements.transfer_credits_details">
                        <button data-balloon-length="xlarge" :aria-label="requirements.transfer_credits_details" data-balloon-pos="up">
                            <svg class="text-gray-500 ml-4 w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <!-- <span v-html="requirements.transfer_credits_details.replace(/(?:\r\n|\r|\n)/g, '<br>')"></span> -->

                    </span>
                </p>
            </span>
        </span>

        <div v-show="noRequirements">
            No admission requirements yet.
        </div>
        
        <span class="text-gray-500" v-if="lastUpdated">Last Updated: {{ lastUpdated }}</span>
    </div>
</template>

<script>
export default {
    name: 'ProgramRequirements',

    props: ['requirements'],

    computed: {
        noRequirements() {
            return _.isEmpty(this.requirements);
        },
        rows() {
            let array = ['tuition'];
            if (this.requirements && this.requirements.tuition_out_state) {
                array.push('tuition_os');
            }
            if (this.requirements && this.requirements.fees) {
                array.push('fees');
            }
            let array2 = [
                'tuition_credit_hour',
                'credits',
                'length',
                'gpa',
                'testing',
                'experience',
                'transfer',
                'usnrank',
                'programrank',
                'notes',
            ];
            return array.concat(array2);
        },
        lastUpdated() {
            if (this.requirements) {
                let timestamp = this.requirements.updated_at;
                if (timestamp) {
                    let date = timestamp.split(' ')[0];
                    let year = date.split('-')[0];
                    return date + ', ' + year;
                }
            }
        },

        singleSchool() {
            let ap = this.focusProgram.partner;
            if (! ap) {
                return '';
            }
            let school = _.filter(this.partners, p => {
                return p.code == ap;
            });
            return school[0];
        },
    },

    methods: {
        heading(row) {
            switch (row) {
                case 'length':
                    return 'Program Length';
                    break;
                case 'credits':
                    return 'Credit Hours';
                    break;
                case 'tuition_os':
                    return 'Tuition Out-of-State'
                    break;
                case 'tuition_credit_hour':
                    return 'Tuition per Credit Hour'
                    break;
                case 'gpa':
                    return 'GPA Required';
                    break;
                case 'testing':
                    return 'Testing Requirements';
                    break;
                case 'experience':
                    return 'Experience Needed';
                    break;
                case 'transfer':
                    return 'Transfer Credits Accepted';
                    break;
                case 'usnrank':
                    return 'US News Ranking';
                    break;
                case 'programrank':
                    return 'Program Rankings';
                    break;
                default:
                    return row;
                    break;
            }
        },
        value(row) {
            if (! this.requirements || this.requirements === undefined) {
                return '';
            }

            let string = '';
            switch (row) {
                case 'length':
                    string = this.requirements.program_months;
                    if (this.requirements.program_months_max) {
                        string += ' - ' + this.requirements.program_months_max;
                    }
                    if (string) {
                        string += ' Months';
                    }
                    break;
                case 'credits':
                    string = this.requirements.credit_hours;
                    if (this.requirements.credit_hours_max) {
                        string += ' - ' + this.requirements.credit_hours_max;
                    }
                    break;
                case 'tuition':
                    string = this.requirements.tuition_in_state;
                    if (string) {
                        string = '$' + string.toLocaleString();
                    }
                    if (this.requirements.tuition_out_state) {
                        string += ' (IS) -  $' + this.requirements.tuition_out_state.toLocaleString() + ' (OS)';
                    }
                    break;
                case 'tuition_credit_hour':
                    string = this.requirements.tuition_credit_hour;
                    if (string) {
                        string = '$' + string.toLocaleString();
                    }
                    if (this.requirements.tuition_credit_hour_os) {
                        string += ' (IS) -  $' + this.requirements.tuition_credit_hour_os.toLocaleString() + ' (OS)';
                    }
                    break;
                case 'tuition_os':
                    string = this.requirements.tuition_out_state;
                    if (string) {
                        string = '$' + string.toLocaleString();
                    }
                    break;
                case 'fees':
                    string = this.requirements.fees;
                    break;
                case 'gpa':
                    string = (this.requirements.gpa_required) ? this.requirements.gpa_required.toFixed(2) : '';
                    break;
                case 'testing':
                    if (this.requirements.testing_required == 'yes') {
                        string = 'Yes: '
                        if (this.requirements.gmat == 'yes') {
                            string += 'GMAT ';
                        }
                        if (this.requirements.gmat_waiver == 'yes') {
                            string += '(waiver)';
                        }
                        if (this.requirements.gre == 'yes') {
                            string += ' GRE ';
                        }
                        if (this.requirements.gre_waiver == 'yes') {
                            string += '(waiver)';
                        }
                    } else if (this.requirements.testing_required == 'no') {
                        string = 'No';
                    }
                    break;
                case 'experience':
                    if (this.requirements.experience_needed) {
                        string = this.requirements.experience_needed;
                        // if (string) {
                        //     string += ' ';
                        // }
                    } else {
                        string = 'None';
                    }
                    break;
                case 'transfer':
                    let t = this.requirements.transfer_credits_accepted;
                    if (t == 'yes') {
                        string = 'Yes';
                    } else if (t == 'no') {
                        string = 'No';
                    }
                    break;
                case 'usnrank': 
                    if (this.usn) {
                        string = String(this.usn.rank) + ' (' + this.usn.category + ')';
                    }
                    break;
                case 'programrank':
                    _.forEach(this.orderedProgramRank, rank => {
                        string += rank.rank_full + '<br>';
                    })
                    break;
                case 'notes':
                    string = `${this.requirements.program_notes}`;
                    break;
                    
                default:
                    break;
            }
            return string;
        }
    },
}
</script>

<style scoped>
</style>
