<template>
    <div>
        <!-- <span v-if="home" class="mt-4">
            <p class="text-gray-500">SEO Home Page</p>
            <p class="text-xl">
                <a :href="'https://' + home.domain + home.slug" target="_blank">
                    {{ home.domain }}{{ home.slug }}
                </a>
            </p>
        </span> -->
            
        <span v-for="(row, i) in rows" :key="i">
            <span v-if="hasPartner">
                <p v-if="row != 'image'" class="text-gray-500 capitalize">{{ rowTitle(row) }}</p>
                <p class="text-xl mb-4">
                    <span v-if="row == 'school'">
                        <inertia-link :href="'/partners/' + partner.code" class="text-blue-500 hover:underline">
                            {{ partner[row] }}
                        </inertia-link>
                    </span>
                    <span v-else-if="row == 'image'">
                        <img 
                            class="h-32 w-32 rounded-full mx-auto" 
                            :src="partnerImage"
                        >
                    </span>
                    <span v-else-if="row == 'url'">
                        <a :href="'https://' + partner.url" target="_blank" class="text-blue-500 hover:underline">
                            {{ partner[row] }}
                        </a>
                    </span>
                    <span v-else-if="row == 'facebook'">
                        <a :href="'https://facebook.com/' + partner.facebook" target="_blank">{{ partner[row] }}</a>
                    </span>
                    <span v-else-if="row == 'notes'">
                        <span class="text-base" v-html="partnerNotes"></span>
                    </span>
                    <span v-else-if="row == 'contacts'">
                        <span class="text-base" v-html="partnerContacts"></span>
                    </span>
                    <span v-else>
                        <span v-html="partner[row]"></span>
                    </span>
                </p>
            </span>
        </span>
    </div>
</template>

<script>
export default {
    name: 'PartnerDetails',

    props: {
        partner: {
            type: Object,
            default: function () {
                return {}
            }
        }
    },

    data() {
        return {
            rows: [
                'school',
                'image',
                'url',
                'facebook',
                'region',
                'city',
                'notes',
                'contacts',
            ],
            home: {}
        }
    },

    computed: {
        partnerNotes() {
            if (! this.partner || ! this.partner.notes) { return '' }
            return this.styleRichText(this.partner.notes)
        },
        partnerContacts() {
            if (! this.partner || ! this.partner.contacts) { return '' }
            return this.styleRichText(this.partner.contacts)
        },
        partnerImage() {
            return (this.partner && this.partner && this.partner.facebook) ? 'https://graph.facebook.com/' + this.partner.facebook + '/picture?type=large' : ''
        },
        seoHome() {
            return 'abcd'
        },
        hasPartner() {
            return ! _.isEmpty(this.partner)
        }
    },

    methods: {
        rowTitle(row) {
            switch (row) {
                case 'url':
                    return 'Partner URL';
                    break;
                case 'seo':
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
            router.push('/partners/' + this.partner.code + '/edit');
        },
        styleRichText(text) {
            let styled = text
                .replace(/<p>/g, '<p class="mb-2">')
                .replace(/<ul>/g, '<ul class="list-disc ml-5 mb-2">')

            return styled
        }
    }
}
</script>
