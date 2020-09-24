<template>
    <div>
        <div class="uk-width-1-5">
            <label>Year</label>
            <multiselect class="" 
                v-model="form['year']"
                :options="years" 
                selectLabel=">" 
                deselectLabel="x">
            </multiselect>
        </div>

        <div>
            <label>Version</label>
            <multiselect class="" 
                v-model="form['scenario']"
                :options="['initial', 'version1', 'version2', 'version3']"
                :allow-empty="false" 
                selectLabel=">" 
                deselectLabel="x">
            </multiselect>
        </div>

        <div class="uk-width-1-5">
            <label>Status</label>
            <multiselect class="" 
                v-model="form['status']"
                :options="['open', 'closed']" 
                :allow-empty="false" 
                selectLabel=">" 
                deselectLabel="x">
            </multiselect>
        </div>

        <div class="uk-width-1-5">
            <label>Note</label>
            <input type="text" class="form-control search-box match-select-height uk-input"  v-model="form['description']" style="height: 40px;">
        </div>

        <div class="uk-width-1-5">
            <button type="primary" class="uk-width-1-1 uk-margin-top" @click.prevent="saveSettings()">Save</button>
        </div>

    </div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import MonthsMixin from '../../Mixins/Months'

export default {
    name: 'BudgetSettings',

    mixins: [MonthsMixin],

    components: {
        Multiselect
    },

    data() {
        return {
            form: {
                year: 2020,
                scenario: '',
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

        getBudgetSettings() {
            axios
                .get('/data/budget/settings')
                .then(({data}) => {
                    this.form.year = data.year;
                    this.form.scenario = data.scenario;
                    this.form.status = data.status;
                    this.form.description = data.description;
                })
        },

        saveSettings() {
            console.log('save these settings');

            axios
                .post('/budget/settings', { 
                    settings: this.form 
                })
                .then(({data}) => {
                    console.log(data);
                    if (data.length > 0) {
                        this.addMessage('Budget Settings Saved!');
                        this.$emit('saved');
                    }
                });
        },

        addMessage(msg) {
            console.log(msg)
        }
    },

    mounted() {
        this.getBudgetSettings();
    }
}
</script>
