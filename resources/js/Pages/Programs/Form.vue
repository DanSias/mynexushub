<template>
    <div class="bg-white mt-8 p-8 container mx-auto rounded shadow lg:w-1/2 md:w-full">
        <table class="table-fixed w-full">
            <tbody>
                <!-- Program Code -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Program Code
                        </label>
                    </td>
                    <td :class="tdClass">
                        <input 
                            v-model="form.code"
                            :class="inputClass" 
                        >
                    </td>
                </tr>

                <!-- Active Status -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Active Status
                        </label>
                    </td>
                    <td :class="tdClass">
                        <ActiveSelect 
                            v-model="form.active" 
                            :placeholder="''"
                            :multiple="false"
                        />
                    </td>
                </tr>

                <!-- Name -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Program Name
                        </label>
                    </td>
                    <td :class="tdClass">
                        <input 
                            v-model="form.name"
                            :class="inputClass" 
                        >
                    </td>
                </tr>

                <!-- Full Name -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Full Name
                        </label>
                    </td>
                    <td :class="tdClass">
                        <input 
                            v-model="form.full_name"
                            :class="inputClass" 
                        >
                    </td>
                </tr>

                <!-- Partner -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Academic Partner
                        </label>
                    </td>
                    <td :class="tdClass">
                        <PartnerSelect 
                            v-model="partnerObject" 
                            :multiple="false"
                            :placeholder="''"
                        />
                    </td>
                </tr>

                <!-- Strategy -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Strategy
                        </label>
                    </td>
                    <td :class="tdClass">
                        <StrategySelect 
                            v-model="form.strategy" 
                            :placeholder="''"
                            :multiple="false"
                        />
                    </td>
                </tr>

                <!-- LTV -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Lifetime Value
                        </label>
                    </td>
                    <td :class="tdClass">
                        <input 
                            v-model="form.ltv"
                            :class="inputClass" 
                        >
                    </td>
                </tr>

                <!-- Location -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Location
                        </label>
                    </td>
                    <td :class="tdClass">
                        <LocationSelect 
                            v-model="form.location" 
                            :placeholder="''"
                            :multiple="false"
                        />
                    </td>
                </tr>

                <!-- BU -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Business Unit
                        </label>
                    </td>
                    <td :class="tdClass">
                        <BuSelect 
                            v-model="form.bu" 
                            :placeholder="''"
                            :multiple="false"
                        />
                    </td>
                </tr>

                <!-- Vertical -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Vertical
                        </label>
                    </td>
                    <td :class="tdClass">
                        <VerticalSelect 
                            v-model="form.vertical" 
                            :placeholder="''"
                            :multiple="false"
                        />
                    </td>
                </tr>

                <!-- Subvertical -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Subvertical
                        </label>
                    </td>
                    <td :class="tdClass">
                        <SubverticalSelect 
                            v-model="form.subvertical" 
                            :vertical="form.vertical"
                            :placeholder="''"
                            :multiple="false"
                        />
                    </td>
                </tr>

                <!-- Level -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Level
                        </label>
                    </td>
                    <td :class="tdClass">
                        <LevelSelect 
                            v-model="form.level" 
                            :placeholder="''"
                            :multiple="false"
                        />
                    </td>
                </tr>

                <!-- Type -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Type
                        </label>
                    </td>
                    <td :class="tdClass">
                        <TypeSelect 
                            v-model="form.type" 
                            :placeholder="''"
                            :multiple="false"
                        />
                    </td>
                </tr>

                <!-- Priority -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Priority
                        </label>
                    </td>
                    <td :class="tdClass">
                        <input class="w-4 h-4 mr-2 leading-tight" type="checkbox" v-model="form.priority">
                        <span class="ml-2">Program is currently a priority</span>
                    </td>
                </tr>

                <!-- Accreditation -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Accreditation
                        </label>
                    </td>
                    <td :class="tdClass">
                        <input 
                            v-model="form.accreditation"
                            :class="inputClass" 
                        >
                    </td>
                </tr>
                <!-- Online Percent -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Online Percent
                        </label>
                    </td>
                    <td :class="tdClass">
                        <multiselect class="search-box" 
                            v-model="form.online_percent"
                            :options="['100% Online', 'Campus-Based Requirement', 'TBD', 'Brand']" 
                            :multiple="false"
                            :close-on-select="true"
                            placeholder=""
                            selectLabel=">" 
                            deselectLabel="x">
                        </multiselect>
                    </td>
                </tr>


                <!-- Start Month -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Start Month
                        </label>
                    </td>
                    <td :class="tdClass">
                        <MonthNumberSelect 
                            v-model="form.start_month" 
                            :placeholder="''"
                            :multiple="false"
                        />
                    </td>
                </tr>
                
                <!-- Start Year -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Start Year
                        </label>
                    </td>
                    <td :class="tdClass">
                        <input 
                            type="number"
                            min="0"
                            v-model.number="form.start_year"
                            :class="inputClass" 
                        >
                    </td>
                </tr>

                <!-- Entry Points -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Entry Points
                        </label>
                    </td>
                    <td :class="tdClass">
                        <input 
                            type="number"
                            min="0"
                            v-model.number="form.entry_points"
                            :class="inputClass" 
                        >
                    </td>
                </tr>

                <!-- Renewal Date -->
                <tr>
                    <td :class="tdClass">
                        <label :class="labelClass">
                            Renewal Date
                        </label>
                    </td>
                    <td :class="tdClass">
                        <datepicker :input-class="inputClass"
                            calendar-class="calendar-above"
                            :placeholder="form.code + ' Contract Renewal Date'" 
                            v-model="renewalDate"
                            :format="'MMMM d, yyyy'"
                        />
                    </td>
                </tr>

                <!-- Program Notes -->
                <tr>
                    <td colspan="2" class="border px-4 py-4">
                        <label class="capitalize text-gray-800 text-lg mt-2">Notes</label>
                        <RichText
                            v-model="form.notes" 
                        />
                    </td>
                </tr>

                <!-- Program Contacts -->
                <tr>
                    <td colspan="2" class="border px-4 py-4">
                        <label class="capitalize text-gray-800 text-lg mt-2">Contacts</label>
                        <RichText
                            v-model="form.contacts" 
                        />
                    </td>
                </tr>

            </tbody>
        </table>
        <div class="">
            <button 
                class="bg-white border-2 text-blue-500 border-blue-500 hover:bg-blue-500 hover:text-white font-bold py-3 px-4 mt-6 rounded w-full transition duration-200" 
                @click.prevent="submitForm()">{{ buttonText }}
            </button>
            <!-- <button type="danger" @click="clearForm()">Clear </button> -->
        </div>
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import Datepicker from 'vuejs-datepicker'
import RichText from '../../Components/Form/RichText'

import ActiveSelect from '../../Components/Select/Active'
import BuSelect from '../../Components/Select/Bu'
import LevelSelect from '../../Components/Select/Level'
import LocationSelect from '../../Components/Select/Location'
import MonthNumberSelect from '../../Components/Select/MonthNumber'
import MonthSelect from '../../Components/Select/Month'
import PartnerSelect from '../../Components/Select/Partner'
import StrategySelect from '../../Components/Select/Strategy'
import SubverticalSelect from '../../Components/Select/Subvertical'
import TypeSelect from '../../Components/Select/Type'
import VerticalSelect from '../../Components/Select/Vertical'

export default {
    name: 'ProgramForm',

    components: {
        Multiselect,
        Datepicker,
        RichText,

        ActiveSelect,
        BuSelect,
        LevelSelect,
        LocationSelect,
        MonthNumberSelect,
        MonthSelect,
        PartnerSelect,
        StrategySelect,
        SubverticalSelect,
        TypeSelect,
        VerticalSelect,
    },

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
                code: '',
                active: '',
                name: '',
                full_name: '',
                partner: '',
                strategy: '',
                ltv: '',
                location: '',
                bu: '',
                vertical: '',
                subvertical: '',
                type: '',
                level: '',
                priority: false,
                accreditation: '',
                online_percent: '',
                start_month: '',
                start_year: '',
                entry_points: '',
                renewal_date: '',
                notes: '',
                contacts: ''
            },
            partnerObject: {},
            monthText: '',

            tdClass: 'border px-4 py-4',
            inputClass: 'bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal',
            labelClass: 'capitalize text-gray-800 text-lg'
        }
    },

    computed: {
        renewalDate: {
            get() {
                let date = this.form.renewal_date;
                if (date == '' || date == null) {
                    return ''
                }
                let array = date.split('-')
                let yr = array[0]
                let mo = array[1] - 1
                let day = array[2]
                let endDateTime = new Date(yr, mo, day)
                return endDateTime
            },
            set(value) {
                console.log(value);
                let date = new Date(value)
                let mo = date.getMonth() + 1
                mo = this.pad(mo, 2)
                let yr = date.getFullYear()
                let day = date.getDate()
                day = this.pad(day, 2)
                let string = String(yr) + '-' + String(mo) + '-' + String(day)
                this.form.renewal_date = string
                console.log(string)
            }
        },
        buttonText() {
            return (this.action == 'edit') ? 'Edit Program' : 'Add Program'
        }
    },

    watch: {
        program() {
            if (Object.keys(this.program).length === 0) {
                this.clearForm()
            } else {
                _.forEach(this.form, (field, key) => {
                    this.form[key] = this.program[key]
                });
            }
        },
        partnerObject() {
            this.form.partner = this.partnerObject.code
        }
    },

    methods: {
        formatKey(key) {
            if (key.includes('_')) {
                let formatted = key.replace('_', ' ');
                return formatted;
            } else {
                return key;
            }
        },

        clearForm() {
            _.forEach(this.form, (pro, key) => {
                this.form[key] = '';
            });
        },

        submitForm() {
            if (this.action == 'edit') {
                axios.patch('/api/programs/' + this.form.id, this.form)
                .then(({data}) => {
                    console.log(data);
                    if (data.id) {
                        _.forEach(this.form, (pro, key) => {
                            this.form[key] = data[key];
                        });
                        this.addMessage('Updates Saved!');
                        this.$emit('close');
                    }
                })
            } else {
                axios.post('/api/programs', this.form)
                .then(({data}) => {
                    console.log(data);
                    if (data.id) {
                        _.forEach(this.form, (pro, key) => {
                            this.form[key] = data[key];
                        });
                        this.addMessage('Updates Saved!');
                        this.$emit('close');
                    }
                })
            }
        },

        addMessage(message) {
            console.log('New Message: ' + message)
        },

        pad(a, b) {
            return(1e15 + a + "").slice(-b);
        },

        populateForm() {
            _.forEach(this.form, (value, property) => {
                let newValue = this.program[property]
                Vue.set(this.form, property, newValue)
            })
            let partnerString = this.program.code.split('-')[0]
            this.partnerObject = {
                id: this.program.partner_id,
                code: partnerString
            } 
        }
    },

    mounted() {
        if (! _.isEmpty(this.program)) {
            this.populateForm()
        }
    }
}
</script>


<style scoped>
tr:nth-child(even) {
    background-color: #f9fafb;
}
.calendar-above {
    bottom: 40px;
}
.vdp-datepicker__calendar {
    bottom: 40px;
}
.rich-text-input {
    background-color: white;
}
</style>
