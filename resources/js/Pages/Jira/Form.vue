<template>
    <div>

        <div class="container mx-auto mt-12">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-medium text-gray-900">
                            Data Request Intake Form
                        </h3> 
                        <p class="mt-1 text-sm text-gray-600">
                            Tell us a little more about this data request of yours...
                        </p>
                        <!-- <p class="mt-6">
                            <pre>
                                <br>
                                {{ formDescription }}
                            </pre>
                        </p> -->
                    </div>
                </div> 
                
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form>
                        <div class="shadow overflow-hidden sm:rounded-md">
                            <div class="px-4 py-5 bg-white sm:p-6">
                                <div class="grid grid-cols-6 gap-4">
                                    <!-- Summary -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <label class="block font-semibold mb-1 text-sm text-gray-700"><span>Request Summary *</span></label> 
                                        <input v-model="form.summary" placeholder="A one sentence title..." class="mb-2 bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal">
                                    </div>

                                    <!-- Department -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <label class="block font-semibold mb-1 text-sm text-gray-700"><span>Department *</span></label> 
                                        <multiselect v-model="form.department" :options="options.department" :multiple="false" open-direction="bottom"></multiselect>
                                    </div>

                                    <!-- Type -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <label class="block font-semibold mb-1 text-sm text-gray-700"><span>Request Type *</span></label> 
                                        <multiselect v-show="form.type != 'Other'" v-model="form.type" :options="options.type" :multiple="false" open-direction="bottom"></multiselect>
                                        <input v-show="form.type == 'Other'" v-model="form.typeOther" placeholder="Input Type (if Other)" ref="typeInput" class="mb-2 bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal">
                                    </div>

                                    <!-- Audience -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <label class="block font-semibold mb-1 text-sm text-gray-700"><span>Audience *</span></label> 
                                        <multiselect v-show="form.audience != 'Other'" v-model="form.audience" :options="options.audience" :multiple="false" open-direction="bottom"></multiselect>
                                        <input v-show="form.audience == 'Other'" v-model="form.audienceOther" placeholder="Input Audience (if Other)" ref="audienceInput" class="mb-2 bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal">
                                    </div>

                                    <!-- Metric -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <label class="block font-semibold mb-1 text-sm text-gray-700"><span>Metrics</span></label> 
                                        <multiselect v-model="form.metric" :options="options.metrics" :multiple="true" open-direction="bottom" :max-height="200"></multiselect>
                                    </div>

                                    <!-- Timeframe -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <label class="block font-semibold mb-1 text-sm text-gray-700"><span>Request Timeframe</span></label> 
                                        <div class="flex justify-between">
                                            <Datepicker input-class=" w-full border border-gray-200 px-4 py-2 text-left rounded focus:border-gray-500 focus:outline-none "
                                                calendar-class=""
                                                placeholder="Start Date" 
                                                v-model="form.timeframe.start"
                                                :minimumView="'month'" 
                                                :maximumView="'month'" 
                                                format="MMMM yyyy"  >
                                            </Datepicker>

                                            <Datepicker input-class=" w-full border border-gray-200 px-4 py-2 text-left rounded focus:border-gray-500 focus:outline-none"
                                                calendar-class=""
                                                placeholder="End Date" 
                                                v-model="form.timeframe.end"
                                                :minimumView="'month'" 
                                                :maximumView="'month'" 
                                                format="MMMM yyyy"  >
                                            </Datepicker>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <label class="block font-semibold mb-1 text-sm text-gray-700"><span>Brief Description of the Request</span></label> 
                                        <textarea v-model="form.description" placeholder="Anything else we should know?" rows="3" class="bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal"></textarea>
                                    </div>

                                    <!-- Delivery -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <label class="block font-semibold mb-1 text-sm text-gray-700"><span>Delivery Method</span></label> 
                                        <multiselect v-model="form.delivery" :options="options.delivery" :multiple="true" open-direction="top" :max-height="200"></multiselect>
                                    </div>

                                    <!-- Due Date -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <label class="block font-semibold mb-1 text-sm text-gray-700"><span>Due Date *</span></label> 
                                        <Datepicker input-class="w-full border border-gray-200 px-4 py-2 text-left rounded focus:border-gray-500 focus:outline-none"
                                            calendar-class=""
                                            placeholder="Completion Date" 
                                            v-model="form.date"
                                            :minimumView="'day'" 
                                            :maximumView="'day'" 
                                            format="MMMM d, yyyy"
                                        />
                                    </div>

                                    <!-- Toggle Filter -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <div class="flex items-center">
                                            <span class="mr-4 text-gray-700 font-semibold mb-1 text-sm"> Include All Programs</span>
                                            <!-- Toggle Button -->
                                            <label 
                                                for="toogleA"
                                                class="flex items-center cursor-pointer"
                                            >
                                                <!-- toggle -->
                                                <div class="relative">
                                                <!-- input -->
                                                <input id="toogleA" type="checkbox" class="hidden" v-model="toggleFilter" />
                                                <!-- line -->
                                                <div
                                                    class="toggle__line w-10 h-4 bg-gray-100 rounded-full shadow-inner"
                                                ></div>
                                                <!-- dot -->
                                                <div
                                                    class="toggle__dot absolute w-6 h-6 bg-gray-300 rounded-full shadow inset-y-0 left-0"
                                                ></div>
                                                </div>
                                                <!-- label -->
                                                <div
                                                class="ml-3 text-gray-700 font-semibold mb-1 text-sm"
                                                >
                                                Filter Programs
                                                </div>
                                            </label>
                                            
                                        </div>
                                    </div>

                                    <!-- Filter -->
                                    <div v-show="toggleFilter" class="col-span-6 sm:col-span-4">
                                        <label class="block font-semibold mb-1 text-sm text-gray-700"><span>Program Filters</span></label> 
                                        <div class="grid grid-cols-3 gap-2 border-gray-200 border-b pb-6">
                                            <LocationFilter 
                                                v-model="form.filter.location" 
                                                :filter="form.filter"
                                                placeholder="Locations"
                                            />
                                            <BuFilter 
                                                v-model="form.filter.bu" 
                                                :filter="form.filter"
                                                placeholder="BU"
                                            />
                                            <PartnerFilter 
                                                v-model="form.filter.partner" 
                                                :filter="form.filter"
                                                placeholder="Partner"
                                            />
                                        </div>
                                    </div>

                                    <!-- Channel -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <label class="block font-semibold mb-1 text-sm text-gray-700"><span>Channels</span></label> 
                                        <ChannelFilter 
                                            v-model="form.filter.channel" 
                                            placeholder="Include All Channels"
                                        />
                                    </div>

                                    <!-- Link -->
                                    <div class="col-span-6 sm:col-span-4">
                                        <label class="block font-semibold mb-1 text-sm text-gray-700"><span>Report Template Link</span></label> 
                                        <input v-model="form.templateLink" placeholder="https://" class="mb-2 bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal">
                                    </div>

                                    <!-- Rush -->
                                    <div class="col-span-6 sm:col-span-4 py-4">
                                        <input type="checkbox" v-model="form.rush"> 
                                        <span class="ml-2"><span :class="(form.rush) ? 'text-red-500 font-bold' : ''">RUSH</span> - High priority request</span>
                                    </div>

                                </div>
                            </div>
                            <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-right sm:px-6">
                                <div class="mr-3">
                                    <div class="text-sm text-gray-600" style="display: none;">
                                        Saved.
                                    </div>
                                </div> 
                                <button class="text-white bg-transparent border border-solid bg-gray-800 hover:bg-gray-600 duration-200 active:bg-gray-600 font-bold text-sm px-4 py-2 rounded-md outline-none focus:outline-none mr-1 mb-1" type="button" style="transition: all .15s ease" @click="submitIssue()">
                                    Submit Data Request
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import Datepicker from "vuejs-datepicker";

import LocationFilter from '../../Components/Select/Location'
import BuFilter from '../../Components/Select/Bu'
import PartnerFilter from '../../Components/Select/Partner'
import ChannelFilter from '../../Components/Select/Channel'

import MonthsMixin from '../../Mixins/Months'

export default {
    name: 'JiraForm',

    components: { 
        Multiselect,
        Datepicker,
        LocationFilter,
        BuFilter,
        PartnerFilter,
        ChannelFilter
    },

    mixins: [MonthsMixin],

    data() {
        return {
            form: {
                summary: '',
                department: '',
                type: '',
                typeOther: '',
                description: '',
                metric: [],
                timeframe: {
                    start: null,
                    end: null
                },
                delivery: '',
                audience: '',
                audienceOther: '',
                recurring: false,
                recipients: [],
                date: null,
                filter: {
                    location: [],
                    bu: [],
                    partner: [],
                    channel: []
                },
                rush: false,
                templateLink: ''
            },
            toggleFilter: false,
            options:  {
                metrics: [
                    'Leads',
                    'Spend',
                    'CPL',
                    'Contact',
                    'Quality',
                    'Starts',
                    'Start Conversion Rate'
                ],
                delivery: [
                    'Excel File',
                    'Tableau',
                    'Meeting'
                ],
                department: [
                    'Marketing',
                    'Finance',
                    'Recruitment Services',
                    'Student Services',
                    'University Partnerships',
                    'Learning & Development',
                    'Other'
                ],
                type: [
                    'New Report',
                    'Existing Report Modification',
                    'Data Analysis',
                    'Data Request',
                    'Data Integration',
                    'New KPI',
                    'New Presentation',
                    'Question',
                    'Other'
                ],
                audience: [
                    'Internal',
                    'External',
                    'Executive Team',
                    'Other'
                ]
            }
        }
    },

    computed: {
        formDescription() {
            let string = ``
            if (this.form.department) {
                string += `Department: ${this.form.department}`
                string += '\n \n'
            }
            if (this.form.type) {
                let typeText = (this.form.type == 'Other') ? this.form.typeOther : this.form.type
                string += `Request Type: ${typeText}`
                string += '\n \n'
            }
            if (this.form.audience) {
                let audienceText = (this.form.audience == 'Other') ? this.form.audienceOther : this.form.audience
                string += `Audience: ${audienceText}`
                string += '\n \n'
            }
            if (this.form.metric.length > 0) {
                let metricString = this.form.metric.toString().replace(/,/g, ', ')
                string += `Metrics: ${metricString}`
                string += '\n \n'
            }
            if (this.form.timeframe.start && this.form.timeframe.end) {
                let start = new Date(this.form.timeframe.start)
                let startMonth = this.months[start.getMonth()]
                startMonth = startMonth.charAt(0).toUpperCase() + startMonth.slice(1)
                let startYear = start.getFullYear()
                
                let end = new Date(this.form.timeframe.end)
                let endMonth = this.months[end.getMonth()]
                endMonth = endMonth.charAt(0).toUpperCase() + endMonth.slice(1)
                let endYear = end.getFullYear()
                if (startMonth == endMonth && startYear == endYear) {
                    string += `Timeframe: ${startMonth} ${startYear} `
                } else {
                    string += `Timeframe: ${startMonth} ${startYear} to ${endMonth} ${endYear}`
                }
                string += '\n \n'
            }
            if (this.form.delivery) {
                let deliveryString = this.form.delivery.toString().replace(/,/g, ', ')
                string += `Delivery Method: ${deliveryString}`
                string += '\n \n'
            }
            if (this.toggleFilter) {
                string += 'Filters: \n'
                if (! _.isEmpty(this.form.filter.location)) {
                    string += 'Location: ' + this.form.filter.location.toString().replace(/,/g, ', ')
                    string += '\n'
                }
                if (! _.isEmpty(this.form.filter.bu)) {
                    string += 'BU: ' + this.form.filter.bu.toString().replace(/,/g, ', ')
                    string += '\n'
                }
                if (! _.isEmpty(this.form.filter.partner)) {
                    string += 'Partner: ' + this.form.filter.partner.toString().replace(/,/g, ', ')
                    string += '\n'
                }
            }
            if (! _.isEmpty(this.form.filter.channel)) {
                string += 'Channels: ' + this.form.filter.channel.toString().replace(/,/g, ', ')
                string += '\n \n'
            }
            if (this.form.rush) {
                string += '\n HIGH PRIORITY \n \n'
            }
            if (this.form.templateLink) {
                string += `Report Template Link: ${this.form.templateLink}`
                string += '\n \n'
            }
            string += (this.form.description) ?  'Description: ' + this.form.description : ''
            return string
        },
        requestType() {
            return this.form.type
        },
        requestAudience() {
            return this.form.audience
        }
    },

    methods: {
        submitIssue() {
            let form = this.form
            form['fullDescription'] = this.formDescription

            axios
                .post('/jira/issues', form)
                .then(({data}) => {
                    console.log(data)
                    this.$emit('close')
                })
        }
    },

    watch: {
        requestType() {
            if (this.form.type == 'Other') {
                this.$nextTick(() => {
                    this.$refs.typeInput.focus()
                })
                console.log('type is other')
            }
        },
        requestAudience() {
            if (this.form.audience == 'Other') {
                this.$nextTick(() => {
                    this.$refs.audienceInput.focus()
                })
                console.log('audience is other')
            }
        }
    }
}
</script>

<style scoped>
.toggle__dot {
  top: -.25rem;
  left: -.25rem;
  transition: all 0.3s ease-in-out;
}

input:checked ~ .toggle__dot {
  transform: translateX(100%);
}
</style>