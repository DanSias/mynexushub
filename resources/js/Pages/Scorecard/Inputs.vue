<template>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-3 gap-4">
            <!-- Degree Card -->
            <div class="bg-white rounded shadow hover:shadow-md duration-200 cursor-pointer" @click="toggleInput('degree')">
                <p class="flex text-gray-800 text-lg py-4 px-6 border-b-2" :class="(showInputs && currentInput == 'degree') ? 'border-blue-500' : 'border-gray-100'">
                    Degree Demand
                    <span class="text-gray-200 hover:text-gray-500 ml-2 duration-200" data data-balloon-pos="right" data-balloon-break data-balloon-length="large" :aria-label="`This section evaluates the market demand for a specific degree program.\n\nWeight: 50%\n\nSources:\n - IPEDS\n - Google Keyword Planner\n - CHEA Accreditation`">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                </p>
                <p v-show="selected.level == '' && selected.degree == ''" class="my-8 text-gray-400 text-center align-middle" >
                    Select Degree Program...
                </p>
                <p v-show="selected.level" class="mx-4 text-gray-800 text-center" :class="(! selected.degree) ? 'my-8' : 'mt-8'">
                    {{ selected.level }}
                </p>
                <p v-show="selected.degree" class="mx-4 text-gray-800 text-center" :class="(! selected.level) ? 'my-8' : 'mb-8'">
                    {{ selected.degree }}
                </p>
            </div>
            <!-- Occupation Card -->
            <div class="bg-white rounded shadow hover:shadow-md duration-200 cursor-pointer" @click="toggleInput('occupation')">
                <p class="flex text-gray-800 text-lg py-4 px-6 border-b-2 border-gray-100" :class="(showInputs && currentInput == 'occupation') ? 'border-blue-500' : 'border-gray-100'">
                    Occupational Outcome
                    <span class="text-gray-200 hover:text-gray-500 ml-2 duration-200" data data-balloon-pos="right" data-balloon-break data-balloon-length="large" :aria-label="`This section section evaluates the size and growth of the occupational outcome associated with this degree program.\n\nWeight: 10%\n\nSources:\n - BLS\n - Burning Glass\n - US News\n - CareerOneStop`">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                </p>
                <p v-if="selected.occupation == ''" class="my-8 text-gray-400 text-center align-middle">
                    Select Occupation...
                </p>
                <p v-if="selected.occupation" class="mx-4 my-8 text-gray-800 text-center">
                    {{ selected.occupation }}
                </p>
            </div>
            <!-- School Card -->
            <div class="bg-white rounded shadow hover:shadow-md duration-200 cursor-pointer"  @click="toggleInput('school')">
                <p class="flex text-gray-800 text-lg py-4 px-6 border-b-2 border-gray-100" :class="(showInputs && currentInput == 'school') ? 'border-blue-500' : 'border-gray-100'">
                    Brand Strength
                    <span class="text-gray-200 hover:text-gray-500 ml-2 duration-200" data data-balloon-pos="right" data-balloon-break data-balloon-length="large" :aria-label="`This section indicates how accessible and well known a university is based on size, admissions, and accolades.\n\nWeight: 40%\n\nSources:\n - US News\n - Google Keyword Planner\n - IPEDS`">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                </p>
                <p v-if="selected.school == ''" class="my-8 text-gray-400 text-center align-middle">
                    Select Institution...
                </p>
                <p v-if="selected.school" class="mx-4 my-8 text-gray-800 text-center">
                    {{ selected.school }}
                </p>
            </div>
        </div>

        <!-- Input Card -->
        <transition name="slide-fade">
            <div v-if="showInputs">
                <!-- Degree -->
                <div v-if="currentInput == 'degree'" class="max-w-4xl mx-auto bg-white rounded shadow mt-8">
                    <div>
                        <nav class="flex border-b border-gray-300">
                            <button 
                                v-for="(tab, i) in degreeTabs" :key="i" 
                                @click="degreeType = tab"
                                :class="(tab == degreeType) ? 'text-blue-500 border-blue-500 border-b-2' : 'text-gray-400 border-b-2 border-transparent hover:text-gray-700 '"
                                class=" -mb-px py-3 px-6 blockfocus:outline-none font-medium duration-200 focus:outline-none">
                                {{ heading(tab) }}
                            </button>
                        </nav>
                    </div>
                    <div class="grid gap-4 grid-cols-3 p-6">
                        <LevelSelect 
                            :currentLevel="selected.level"
                            @setLevel="setLevel"
                        />
                        <CodeSelect 
                            class="col-span-2"
                            :currentDegree="selected.degree"
                            :degreeLevel="selected.level"
                            :degreeType="degreeType"
                            @setCode="setCode"
                        />
                    </div>
                </div>
                <!-- Occupation -->
                <div v-if="currentInput == 'occupation'" class="max-w-4xl mx-auto bg-white rounded shadow mt-8">
                    <div>
                        <nav class="flex border-b border-gray-300">
                            <button 
                                v-for="(tab, i) in occupationTabs" :key="i" 
                                @click="occupationType = tab"
                                :class="(tab == occupationType) ? 'text-blue-500 border-blue-500 border-b-2 ' : 'text-gray-400 border-b  hover:text-gray-700'"
                                class=" -mb-px py-3 px-6 block hover:text-blue-500 focus:outline-none font-medium">
                                {{ heading(tab) }}
                            </button>
                        </nav>
                    </div>
                    <div class="p-6">
                        <OccupationSelect 
                            :occupationType="occupationType"
                            :degreeCode="selected.degree"
                            :currentOccupation="selected.occupation"
                            @setOccupation="setOccupation"
                        />
                    </div>
                </div>
                <!-- School -->
                <div v-if="currentInput == 'school'" class="max-w-4xl mx-auto bg-white rounded shadow mt-8">
                    <div>
                       <nav class="flex border-b border-gray-300">
                            <button 
                                v-for="(tab, i) in schoolTabs" :key="i" 
                                @click="schoolType = tab"
                                :class="(tab == schoolType) ? 'text-blue-500 border-blue-500 border-b-2 ' : 'text-gray-400 border-b  hover:text-gray-700'"
                                class=" -mb-px py-3 px-6 block hover:text-blue-500 focus:outline-none font-medium">
                                {{ heading(tab) }}
                            </button>
                        </nav>
                    </div>
                    <div class="p-6">
                        <SchoolSelect 
                            :schoolType="schoolType"
                            :currentSchool="selected.school"
                            @setSchool="setSchool"
                        />
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
import LevelSelect from './SelectLevel'
import CodeSelect from './SelectCode'
import OccupationSelect from './SelectOccupation'
import SchoolSelect from './SelectSchool'

export default {
    name: 'ScorecardInputs',

    props: {
        selected: {
            type: Object,
            default: function() {
                return {}
            }
        }
    },

    components: {
        LevelSelect,
        CodeSelect,
        OccupationSelect,
        SchoolSelect
    },

    data() {
        return {
            showInputs: true,
            currentInput: 'school',
            degreeType: 'keywords',
            degreeTabs: ['keywords', 'conferrals', 'codes'],
            occupationType: 'proxy',
            occupationTabs: ['proxy', 'jobs'],
            schoolType: 'all',
            schoolTabs: ['all', 'partners']
        }
    },

    methods: {
        heading(term) {
            switch (term) {
                case 'keywords':
                    return 'Degree Search Term'
                    break;
                case 'conferrals':
                    return 'Conferrals'
                    break;
                case 'codes':
                    return 'All CIP Codes'
                    break;
                case 'proxy':
                    return 'Proxy Occupations'
                    break;
                case 'jobs':
                    return 'All Occupations'
                    break;
                case 'all':
                    return 'All Schools'
                    break;
                case 'partners':
                    return 'Academic Partners'
                    break;
            
                default:
                    break;
            }
        },
        toggleInput(value) {
            if (this.currentInput == value && this.showInputs == true) {
                this.showInputs = ! this.showInputs
            } else if (this.showInputs == false) {
                this.showInputs = true
            }
            this.currentInput = value
        },
        // Emit Select Values
        setLevel(level) {
            this.$emit('setLevel', level)
        },
        setCode(code) {
            this.$emit('setCode', code)
        },
        setOccupation(occupation) {
            this.$emit('setOccupation', occupation)
        },
        setSchool(school) {
            this.$emit('setSchool', school)
        },
    },
}
</script>

<style scoped>
/* Enter and leave animations can use different */
/* durations and timing functions.              */
.slide-fade-enter-active {
  transition: all .3s ease;
}
.slide-fade-leave-active {
  transition: all .3s ease;
}
.slide-fade-enter, .slide-fade-leave-to
/* .slide-fade-leave-active below version 2.1.8 */ {
  transform: translateY(-20px);
  opacity: 0;
}
</style>