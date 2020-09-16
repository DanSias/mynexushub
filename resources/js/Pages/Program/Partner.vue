<template>
    <div>
        <div>
            <span v-if="program && program.partner && program.partner.facebook">
                <div class="uk-flex uk-flex-center uk-margin-small-bottom">
                    <img class="uk-border-circle partner-image" :src="'https://graph.facebook.com/' + program.partner.facebook + '/picture?type=large'">
                </div>
            </span>
            <span v-if="program.home" class="mt-4">
                <p class="text-gray-500">SEO Home Page</p>
                <p class="text-xl">
                    <a :href="'https://' + program.home.domain + program.home.slug" target="_blank">
                        {{ program.home.domain }}{{ program.home.slug }}
                    </a>
                </p>
            </span>
                
            <span v-for="(row, i) in partnerRows" :key="i">
                <span v-if="program && program.partner && program.partner[row]">
                    <p class="text-gray-500 capitalize">{{ rowTitle(row) }}</p>
                    <p class="text-xl mb-4">
                        <span v-if="row == 'school'">
                            <a :href="'/partners/' + program.partner.code" class="text-blue-500">{{ program.partner[row] }}</a>
                        </span>
                        <span v-else-if="row == 'url'">
                            <a :href="'https://' + program.partner.url" class="text-blue-500">{{ program.partner[row] }}</a>
                        </span>
                        <span v-else-if="row == 'facebook'">
                            <a :href="'https://facebook.com/' + program.partner.facebook" target="_blank">{{ program.partner[row] }}</a>
                        </span>
                        <span v-else-if="row == 'notes'">
                            <span class="text-base" v-html="program.partner.notes"></span>
                        </span>
                        <span v-else-if="row == 'contacts'">
                            <span class="text-base" v-html="program.partner.contacts"></span>
                        </span>
                        <span v-else>
                            <span v-html="program.partner[row]"></span>
                        </span>
                    </p>
                </span>
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
                'location',
                'bu',
                'vertical',
                'subvertical',
                'type',
                'started',
                'accreditation',
            ],
            partnerRows: [
                'school',
                'url',
                'facebook',
                'region',
                'city',
                'notes',
                'contacts',
            ],
            deadlines: {}
        }
    },

    computed: {
        calendarYear() {
            return 1234
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
    }
}
</script>
