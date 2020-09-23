<template>
    <div>
        <ProgramForm 
            v-show="formType == 'attributes'"
            :program="program"
            :action="'edit'"
        />

        <RequirementsForm 
            v-show="formType == 'requirements'"
            :program="program"
            :action="'edit'"
        />

        <div v-show="formType == 'tracks'" class="grid grid-cols-2 gap-4 container mx-auto">
            <TracksForm
                :program="program"
            />
            <ConcentrationsForm
                :program="program"
            />
        </div>

        <div v-show="formType == 'codes'" class="grid grid-cols-1 gap-4 container mx-auto lg:w-1/3">
            <CodesForm
                :program="program"
            />
        </div>
        <p>&nbsp;</p>
    </div>
</template>

<script>
import ProgramForm from './Form'
import RequirementsForm from './FormRequirements'
import ConcentrationsForm from './FormConcentrations'
import TracksForm from './FormTracks'
import CodesForm from './FormCodes'

export default {
    name: 'ProgramsEdit',

    props: {
        program: {
            type: Object,
            default: () => {}
        },
        formType: {
            type: String,
            default: ''
        }
    },

    components: {
        ProgramForm,
        RequirementsForm,
        ConcentrationsForm,
        TracksForm,
        CodesForm
    }
}
</script>

<style>

</style>