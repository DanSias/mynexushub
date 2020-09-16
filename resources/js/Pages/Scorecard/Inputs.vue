<template>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded shadow hover:shadow-md cursor-pointer" @click="toggleInput('degree')">
                <p class="text-gray-800 text-lg py-4 px-6 border-b-2 border-gray-100">Degree Demand</p>
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

            <div class="bg-white rounded shadow hover:shadow-md cursor-pointer" @click="toggleInput('occupation')">
                <p class="text-gray-800 text-lg py-4 px-6 border-b-2 border-gray-100">Occupational Outcome</p>
                <p v-if="selected.occupation == ''" class="my-8 text-gray-400 text-center align-middle">
                    Select Occupation...
                </p>
                <p v-if="selected.occupation" class="mx-4 my-8 text-gray-800 text-center">
                    {{ selected.occupation }}
                </p>
            </div>
            <div class="bg-white rounded shadow hover:shadow-md cursor-pointer"  @click="toggleInput('school')">
                <p class="text-gray-800 text-lg py-4 px-6 border-b-2 border-gray-100">Brand Strength</p>
                <p v-if="selected.school == ''" class="my-8 text-gray-400 text-center align-middle">
                    Select Institution...
                </p>
                <p v-if="selected.school" class="mx-4 my-8 text-gray-800 text-center">
                    {{ selected.school }}
                </p>
            </div>
        </div>

        <div v-if="showInputs">
            <div v-if="currentInput == 'degree'" class="max-w-4xl mx-auto bg-white rounded shadow px-6 py-4 mt-8">
                <div>
                    <nav class="flex">
                        <button 
                            v-for="(tab, i) in degreeTabs" :key="i" 
                            @click="degreeType = tab"
                            :class="(tab == degreeType) ? 'text-blue-500 border-blue-500 ' : 'text-gray-600 border-white'"
                            class="py-2 px-6 block hover:text-blue-500 border-b-2 focus:outline-none font-medium">
                            {{ heading(tab) }}
                        </button>
                    </nav>
                </div>
                <div class="grid gap-3 grid-cols-3 pt-6 pb-2">
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

            <div v-if="currentInput == 'occupation'" class="max-w-4xl mx-auto bg-white rounded shadow px-6 py-4 mt-8">
                <div>
                    <nav class="flex">
                        <button 
                            v-for="(tab, i) in occupationTabs" :key="i" 
                            @click="occupationType = tab"
                            :class="(tab == occupationType) ? 'text-blue-500 border-blue-500 ' : 'text-gray-600 border-white'"
                            class="py-2 px-6 block hover:text-blue-500 border-b-2 focus:outline-none font-medium">
                            {{ heading(tab) }}
                        </button>
                    </nav>
                </div>
                <div class="pt-6 pb-2">
                    <OccupationSelect 
                        :occupationType="occupationType"
                        :degreeCode="selected.degree"
                        :currentOccupation="selected.occupation"
                        @setOccupation="setOccupation"
                    />
                </div>
            </div>

            <div v-if="currentInput == 'school'" class="max-w-4xl mx-auto bg-white rounded shadow px-6 py-4 mt-8">
                <div>
                    <nav class="flex">
                        <button 
                            v-for="(tab, i) in schoolTabs" :key="i" 
                            @click="schoolType = tab"
                            :class="(tab == schoolType) ? 'text-blue-500 border-blue-500 ' : 'text-gray-600 border-white'"
                            class="py-2 px-6 block hover:text-blue-500 border-b-2 focus:outline-none font-medium">
                            {{ heading(tab) }}
                        </button>
                    </nav>
                </div>
                <div class="pt-6 pb-2">
                    <SchoolSelect 
                        :schoolType="schoolType"
                        :currentSchool="selected.school"
                        @setSchool="setSchool"
                    />
                </div>
            </div>
        </div>
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
            showInputs: false,
            currentInput: '',
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

<style>

</style>