<template>
    <div class="flex items-center justify-between -mx-2 mt-24 mb-48">
        <div class="w-full mx-2">
            <label>Year</label>
            <multiselect class="" 
                v-model="form['year']"
                :options="years" 
                selectLabel=">" 
                deselectLabel="x">
            </multiselect>
        </div>

        <div class="w-full mx-2">
            <label>Month</label>
            <multiselect class="" 
                v-model="form['month']"
                :options="months"
                :allow-empty="false" 
                selectLabel=">" 
                :maxHeight="170"
                deselectLabel="x">
            </multiselect>
        </div>

        <div class="w-full mx-2">
            <label>Status</label>
            <multiselect class="" 
                v-model="form['status']"
                :options="['open', 'closed']" 
                :allow-empty="false" 
                selectLabel=">" 
                deselectLabel="x">
            </multiselect>
        </div>

        <div class="w-full mx-2">
            <label>Note</label>
            <input type="text" class="border border-gray-200 rounded focus:outline-none px-3"  v-model="form['description']" style="height: 40px;">
        </div>

        <div class="w-full mx-2">
            <label>&nbsp;</label>
            <button type="primary" class="bg-gray-800 rounded text-white w-full px-3 py-2" @click.prevent="saveSettings()">Save</button>
        </div>

    </div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import MonthsMixin from '../../Mixins/Months'

export default {
    name: 'ForecastSettings',

    mixins: [MonthsMixin],

    components: {
        Multiselect
    },

    data() {
        return {
            form: {
                year: 2020,
                month: '',
                status: '',
                description: '',
            }
        }
    },
    computed: {
        years() {
            let d = new Date;
            let yr = d.getFullYear();
            let nextYear = parseInt(yr) + 1;
            return [nextYear, yr];
        }
    },

    watch: {
        year() {
            this.fillForm();
        },
        status() {
            this.fillForm();
        },
        scenario() {
            this.fillForm();
        },
        description() {
            this.fillForm();
        }
    },

    methods: {

        fillForm() {
            this.form.year = this.year;
            this.form.status = this.status;
            this.form.scenario = this.scenario;
            this.form.description = this.description;
        },

        getSettings() {
            axios
                .get('/data/forecast/settings')
                .then(({data}) => {
                    this.form.year = data.year;
                    this.form.month = data.month;
                    this.form.status = data.status;
                    this.form.description = data.description;
                })
        },

        saveSettings() {
            console.log('save these settings');

            axios
                .post('/data/forecast/settings', { 
                    settings: this.form 
                })
                .then(({data}) => {
                    console.log(data);
                    if (data.length > 0) {
                        this.addMessage('Forecast Settings Saved!');
                        this.$emit('saved');
                    }
                });
        },

        addMessage(msg) {
            console.log(msg)
        }
    },

    mounted() {
        this.getSettings();
    }
}
</script>
