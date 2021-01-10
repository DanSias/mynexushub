<template>
    <div v-if="this.projectId">
        <div class="container mx-auto mt-8">
            <div class="bg-white p-6">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Summary</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Label</th>
                            <th class="px-4 py-2">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(issue, j) in projectIssues" :key="j" class="hover:bg-yellow-50" :class="(j % 2 == 0) ? 'bg-gray-50' : ''">
                            <td class="border px-4 py-2">
                                <span class="cursor-pointer hover:text-blue-500" @click="setIssue(issue)">{{ issue.fields.summary }}</span>
                            </td>
                            <td class="border px-4 py-2">
                                {{ issue.fields.status.name }}
                            </td>
                            <td class="border px-4 py-2">
                                <span v-if="issue.fields.labels.length > 0">
                                    <span v-for="(label, k) in issue.fields.labels" :key="k"> {{ label }} </span>
                                </span>
                            </td>
                            <td class="border px-4 py-2">
                                {{ issue.fields.created | dateFj }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="projectIssues.length == 0">No Issues Yet</div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'JiraIssuesList',

    data() {
        return {
            issues: []
        }
    },

    props: {
        issueId: { 
            type: [String, Number],
            default: 0
        },
        projectId: { 
            type: [String, Number],
            default: 0
        }
    },

    computed: {
        projectIssues() {
            return _.filter(this.issues, (issue) => {
                return issue.fields.project.id == this.projectId
            })
        },
    },

    methods: {
        getIssues() {
            axios
                .get('/jira/issues/' + this.projectId)
                .then(({data}) => this.issues = data.issues)
        },

        setIssue(issue) {
            this.$emit('setIssue', issue)
        }
    },

    mounted() {
        this.getIssues()
    }
}
</script>

<style>

</style>