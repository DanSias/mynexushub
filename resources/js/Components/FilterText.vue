<template>
    <div>
        {{ filterText }}
    </div>
</template>

<script>
export default {
    name: 'FilterText',

    props: {
        filter: {
            type: Object,
            default: () => {}
        }
    },

    computed: {
        filterText() {
            let string = ''

            if (this.filter.location) {
                string += this.filter.location.toString()
            }

            if (this.filter.bu && ! _.isEmpty(this.filter.bu)) {
                string = 'BU ' + this.filter.bu.toString()
            }

            if (this.filter.partner && ! _.isEmpty(this.filter.partner)) {
                string = this.filter.partner.toString()
            }


            string += ' '

            if (this.filter.channel) {
                string += this.filter.channel.toString()
            }

            return string.replace(/,/g, ', ')
        }
    },
}
</script>

<style>

</style>