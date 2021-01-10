<template>
    <div v-if="! projectId">
        <div class="container mx-auto mt-8">
            <div class="grid grid-cols-2 gap-5">
                <div v-for="(project, i) in projects" :key="i">
                    <div class="bg-white p-8 flex cursor-pointer shadow hover:shadow-lg duration-200" @click="setProject(project)">
                        <div class="">
                            <img :src="projectImage(project)" class="h-20 w-20 rounded-full">
                        </div>
                        <div>
                            <h2 class="ml-6 text-xl mt-1 mb-auto">{{ project.name}}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'JiraProjectList',

    data() {
        return {
            projects: []
        }
    },

    props: {
        projectId: { 
            type: [String, Number],
            default: 0
        }
    },

    methods: {
        getProjects() {
            axios
                .get('/jira/projects')
                .then(({data}) => this.projects = data.values)
        },

        projectImage(project) {
            if (project && project.avatarUrls && project.avatarUrls['48x48']) {
                return project.avatarUrls['48x48']
            }
        },
        setProject(project) {
            this.$emit('setProject', project)
        }
    },

    mounted() {
        this.getProjects()
    }
}
</script>

<style>

</style>