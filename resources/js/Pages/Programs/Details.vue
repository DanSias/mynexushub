<template>

    <div>
        <div>
            <span v-for="(row, i) in rows" :key="i">
                <span v-if="row == 'location'">
                    <div class="flex">
                        <div class="w-1/2">
                            <p class="capitalize text-gray-500">{{ rowTitle(row) }}</p>
                            <p class="text-xl mb-4">
                                {{ program[row] }}
                            </p>
                        </div>
                        <div class="w-1/2">
                            <p class="text-gray-500">Business Unit</p>
                            <p class="text-xl mb-4">
                                {{ program['bu'] }}
                            </p>
                        </div>
                    </div>
                </span>
                <span v-else-if="row == 'home' && program.home">
                    <p class="capitalize text-gray-500">{{ rowTitle(row) }}</p>
                    <p class="text-xl mb-4">
                        <a :href="'https://' + program.home.domain + program.home.slug" target="_blank" class="text-blue-500 hover:underline">
                            {{ program.home.domain }}{{ program.home.slug }} 
                        </a>
                    </p>
                </span>
                <span v-else-if="row != 'bu'">
                    <p class="capitalize text-gray-500">{{ rowTitle(row) }}</p>
                    <p class="text-xl mb-4">
                        {{ program[row] }} 
                        <span v-if="row == 'type' && program.level" class="capitalize">
                            ({{ program['level'] }})
                        </span>
                    </p>
                </span>
            </span>

            <!-- Enterprise Details-->
            <span v-if="program.enterprise">
                <div class="mb-2">
                    <div class="w-1/3" v-if="program.enterprise.pod">
                        <p class="text-gray-500">Pod #</p>
                        <p class="text-xl">
                            {{ program.enterprise.pod }}
                        </p>
                    </div>
                    <div v-if="program.enterprise.quadrant">
                        <p class="text-gray-500">Quadrant</p>
                        <p class="text-xl">
                            {{ program.enterprise.quadrant }}
                        </p>
                    </div>
                </div>
                <!-- College -->
                <p class="text-gray-500">College</p>
                <p class="text-xl">
                    {{ program.enterprise.college }}
                </p>
            </span>

            <!-- Tracks and Concentrations -->
            <span v-if="trackCount > 0 || concentrationCount > 0">
                <div class="mb-2">
                    <div class="" v-if="concentrationCount">
                        <p class="text-gray-500">Concentrations</p>
                        <p class="text-xl">
                            {{ concentrationCount }}
                        </p>
                        <vk-drop :delay-hide="100">
                            <div>
                                <ul>
                                    <li v-for="(c, i) in program.concentrations" :key="i">
                                        {{ c.concentration }}
                                    </li>
                                </ul>
                            </div>
                        </vk-drop>
                    </div>
                    <div v-if="trackCount">
                        <p class="text-gray-500">Tracks</p>
                        <p class="text-xl">
                            {{ trackCount }}
                        </p>
                        <vk-drop :delay-hide="100">
                            <div>
                                <ul>
                                    <li v-for="(t, i) in program.tracks" :key="i">
                                        {{ t.track }}
                                    </li>
                                </ul>
                            </div>
                        </vk-drop>
                    </div>
                </div>
            </span>

            <!-- Rich Text fields for notes and contacts -->
            <span v-show="program.notes">
                <p class="text-gray-500">Notes</p>
                <span v-html="program.notes"></span>
            </span>
            <span v-show="program.contacts">
                <p class="text-gray-500">Contacts</p>
                <span v-html="program.contacts"></span>
            </span>
        </div>

        <div class="mt-4" v-show="hasThisYearDeadlines">
            <h4>{{ program.code }} {{ calendarYear }} App Deadlines</h4>
            <span v-for="(dl, i) in thisYearDeadlines" :key="i">
                <p class="mb-0 " :class="{'text-gray-500' : dl.past, 'bold': ! dl.past}">
                    {{ dl.semester }} {{ dl.term }} - {{ dl.text }}
                </p>
                <p class="text-gray-500 ml-2">{{ dl.when }}</p>
            </span>
        </div>
    </div>

</template>

<script>
export default {
    name: 'ProgramDetails',

    props: {
        program: {
            type: Object,
            default: function () {
                return {}
            }
        }
    },

    data() {
        return {
            rows: [
                'name',
                'home',
                'location',
                'bu',
                'vertical',
                'subvertical',
                'type',
                'started',
                'accreditation',
            ],
            deadlines: {}
        }
    },

    computed: {
        calendarYear() {
            return new Date().getFullYear();
        },
        code() {
            return this.program.code;
        },
        thisYearDeadlines() {
            return _.filter(this.deadlines, ['year', this.calendarYear]);
        },
        hasThisYearDeadlines() {
            if (! _.isEmpty(this.thisYearDeadlines)) {
                return true;
            }
            return false;
        },

        trackCount() {
            return (this.program && this.program.tracks) ?  this.program.tracks.length : 0;
        },
        concentrationCount() {
            return (this.program && this.program.concentrations) ?  this.program.concentrations.length : 0;
        }
    },

    methods: {
        rowTitle(row) {
            switch (row) {
                case 'url':
                    return 'Partner URL';
                    break;
                case 'home':
                    return 'SEO Home Page';
                    break;
                case 'value':
                    return '';
                    break;
                case 'started':
                    return 'Marketing Began';
                    break;
            
                default:
                    return row;
                    break;
            }
        },
        goToEditPage() {
            const router = this.$router;
            router.push('/partners/' + this.program.partner.code + '/edit');
        },
        getDeadlines() {
            axios
                .get('/deadlines/program/' + this.program.code)
                .then(response => this.deadlines = response.data);
        }
    },
    watch: {
        code() {
            this.getDeadlines();
        }
    },
    mounted() {
        this.getDeadlines();
    }
}
</script>
