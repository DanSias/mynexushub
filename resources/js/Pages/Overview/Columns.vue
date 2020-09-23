<template>
    <div class="bg-white rounded shadow p-6">
        <div>
            <div class="grid grid-cols-6 gap-4">
                <div v-for="(column, i) in columns" :key="i" class="uk-margin-small-top">
                    <input class="uk-checkbox" type="checkbox" v-model="column.visible"> 
                    {{ noBreak(column.label) }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'OverviewColumns',

    props: {
        show: {
            type: Array,
            default: function () {
                return [];
            }
        },
        hide: {
            type: Array,
            default: function () {
                return [];
            }
        }
    },

    data() {
        return {
            columns: {
                program: {
                    label: 'Program',
                    visible: true
                },
                semester: {
                    label: 'Semester',
                    visible: true
                },
                term: {
                    label: 'Term',
                    visible: true,
                },
                leads: {
                    label: 'Leads',
                    visible: true,
                },
                spend: {
                    label: 'Spend',
                    visible: false,
                },
                cpl: {
                    label: 'CPL',
                    visible: false,
                },
                contact: {
                    label: 'Contact',
                    visible: false,
                },
                contact15: {
                    label: 'Contact 15',
                    visible: true,
                },
                contact15Rate: {
                    label: 'Contact<br>15 Rate',
                    visible: true,
                },
                quality30Rate: {
                    label: 'Quality<br>30 Rate',
                    visible: true,
                },
                insalesAppRate: {
                    label: 'InSales<br>to App %',
                    visible: true,
                },
                cvrsTarget: {
                    label: 'CVRS<br>Target',
                    visible: true,
                },
                cvrsForecast: {
                    label: 'CVRS<br>Forecast',
                    visible: true,
                },
                cvrsVariance: {
                    label: 'CVRS Pct.<br>Variance',
                    visible: true,
                },
                startsTarget: {
                    label: 'Starts<br>Target',
                    visible: true,
                },
                startsForecast: {
                    label: 'Starts<br>Forecast',
                    visible: true,
                },
                startsVariance: {
                    label: 'Starts<br>Variance',
                    visible: true,
                },
                studentsTarget: {
                    label: 'Students<br>Target',
                    visible: false,
                },
                studentsForecast: {
                    label: 'Students<br>Forecast',
                    visible: false,
                },
                studentsVariance: {
                    label: 'Students<br>Variance',
                    visible: false,
                },
                revenueTarget: {
                    label: 'Revenue<br>Target',
                    visible: false,
                },
                revenueForecast: {
                    label: 'Revenue<br>Forecast',
                    visible: false,
                },
                revenueVariance: {
                    label: 'Revenue<br>Variance',
                    visible: false,
                }
            }
        }
    },

    computed: {
        columnList() {
            return _.keys(this.columns);
        },
        columnString() {
            let string = '';
            _.forEach(this.columns, column => {
                string += (column.visible) ? column.label : '';
            });
            return string;
        }
    },

    methods: {
        noBreak(string) {
            return string.replace('<br>', ' ');
        }
    },

    watch: {
        columnString() {
            let programColumnString = JSON.stringify(this.columns);
            localStorage.setItem('overviewColumns', programColumnString);
            this.$emit('setColumns', this.columns);
        },
    },

    mounted() {
        if (localStorage.overviewColumns) {
            let userColumns = JSON.parse(localStorage.overviewColumns);
            _.forEach(userColumns, (col, key) => {
                if (col.visible) {
                    this.columns[key].visible = true;
                }
            });
        }
        if (! _.isEmpty(this.show)) {
            _.forEach(this.show, s => {
                if (this.columns[s]) {
                    this.columns[s].visible = true;
                }
            })
        }
        if (! _.isEmpty(this.hide)) {
            _.forEach(this.hide, h => {
                if (this.columns[h]) {
                    this.columns[h].visible = false;
                }
            })
        }
        this.$emit('setColumns', this.columns);
    },
}
</script>

<style>

</style>