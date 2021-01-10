<template>
    <app-layout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Degree Scorecard
                </h2>
                <div class="flex">
                    <SaveIcon 
                        :selected="selected"
                        aria-label="Save Current Scorecard" data-balloon-pos="down-right"
                    />
                    <LoadIcon 
                        aria-label="Load Previous Scorecard" data-balloon-pos="down-right"
                    />
                </div>
            </div>
        </template>

        <ScorecardInputs 
            class="py-12"
            :selected="selected"
            @setLevel="setLevel"
            @setCode="setCode"
            @setOccupation="setOccupation"
            @setSchool="setSchool"
        />

        <!-- Score Details -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between -mx-4">
                <DegreeScore 
                    class="w-1/3 mx-4"
                    :level="selected.level"
                    :degree="selected.degree"
                    :school="selected.school"
                />
                <OccupationScore 
                    class="w-1/3 mx-4"
                    :occupation="selected.occupation"
                />
                <SchoolScore 
                    class="w-1/3 mx-4"
                    :school="selected.school"
                />
            </div>
        </div>

    </app-layout>
</template>

<script>
import AppLayout from './../Layouts/AppLayout'

import SaveIcon from './Scorecard/IconSave'
import LoadIcon from './Scorecard/IconLoad'

import ScorecardInputs from './Scorecard/Inputs'

import DegreeScore from './Scorecard/ScoreDegree'
import OccupationScore from './Scorecard/ScoreOccupation'
import SchoolScore from './Scorecard/ScoreSchool'

export default {
    name: 'Scorecard',

    components: {
        SaveIcon,
        LoadIcon,
        AppLayout,
        ScorecardInputs,
        DegreeScore,
        OccupationScore,
        SchoolScore
    },

    data() {
        return {
            selected: {
                level: '',
                degree: '',
                occupation: '',
                school: ''
            },
            score: {
                degree: 0,
                occupation: 0,
                school: 0
            }
        }
    },

    methods: {
        setLevel(level) {
            this.selected.level = level
        },
        setCode(degree) {
            this.selected.degree = degree
        },
        setOccupation(occupation) {
            this.selected.occupation = occupation
        },
        setSchool(school) {
            this.selected.school = school
        }
    },
}
</script>
