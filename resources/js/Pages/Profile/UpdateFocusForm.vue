<template>
    <jet-form-section >
        <template #title>
            Team &amp; Focus Information
        </template>

        <template #description>
            Update your current team, role, and area of focus 
        </template>

        <template #form>
            <!-- Team -->
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="team" value="Team" />
                <team-select v-model="form.team" />
            </div>

            <!-- Title -->
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="title" value="Role Title" />
                <role-select :team="form.team" v-model="form.title" />
            </div>

            <!-- Location -->
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="location" value="Focus Locations" />
                <location-select :team="form.team" v-model="form.location" />
            </div>

            <!-- Bu -->
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="bu" value="Focus Business Units" />
                <bu-select :team="form.team" v-model="form.bu" :max="170" :filter="form" />
            </div>

            <!-- Channel -->
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="channel" value="Focus Channels" />
                <channel-select :team="form.team" v-model="form.channel" :open="'top'" />
            </div>
            
            <div class="my-6">&nbsp;</div>
        </template>


        <template #actions>
            <div class="text-sm text-gray-600 transition duration-200 mr-3" v-if="showSuccessMessage">
                Saved.
            </div>
            <button @click="updateRoleInformation()" :class="{ 'opacity-25': processing }" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150">SAVE</button>
            <!-- <jet-button @click="updateRoleInformation" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </jet-button> -->
        </template>
    </jet-form-section>
</template>

<script>
    import JetButton from './../../Jetstream/Button'
    import JetFormSection from './../../Jetstream/FormSection'
    import JetInput from './../../Jetstream/Input'
    import JetInputError from './../../Jetstream/InputError'
    import JetLabel from './../../Jetstream/Label'
    import JetActionMessage from './../../Jetstream/ActionMessage'
    import JetSecondaryButton from './../../Jetstream/SecondaryButton'

    import TeamSelect from '../../Components/Select/Team'
    import RoleSelect from '../../Components/Select/Role'
    import LocationSelect from '../../Components/Select/Location'
    import BuSelect from '../../Components/Select/Bu'
    import ChannelSelect from '../../Components/Select/Channel'

    export default {
        components: {
            JetActionMessage,
            JetButton,
            JetFormSection,
            JetInput,
            JetInputError,
            JetLabel,
            JetSecondaryButton,
            TeamSelect,
            RoleSelect,
            LocationSelect,
            BuSelect,
            ChannelSelect
        },

        props: ['email'],

        data() {
            return {
                form: {
                    team: this.team,
                    title: this.title,
                    location: this.location,
                    bu: this.bu,
                    channel: this.channel,
                },
                processing: false,
                showSuccessMessage: false
            }
        },

        methods: {
            updateRoleInformation() {
                this.processing = true
                axios
                    .post('/user/role/' + this.email, this.form )
                    .then(({data}) => {
                        this.processing = false
                        this.showMessage()
                        this.getProfileInformation()
                    })
            },
            getProfileInformation() {
                axios
                    .get('/user/role')
                    .then(({data}) => {
                        let loop = ['team', 'title', 'location', 'bu', 'channel']
                        _.forEach(loop, property => {
                            if (data[property]) {
                                this.form[property] = data[property]
                            }
                        })
                    })
            },
            showMessage() {
                this.showSuccessMessage = true
                setTimeout(() => { this.showSuccessMessage = false}, 3000)
            }
        },

        mounted() {
            this.getProfileInformation()
        }
    }
</script>
