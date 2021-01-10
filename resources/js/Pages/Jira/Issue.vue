<template>
    <div>
        <div v-if="hasIssue" class="container max-w-4xl mx-auto -mt-4 ">
            <!-- Card Header -->
            <div class="flex border-b border-gray-200 py-4 px-6">
                <h2 class=" text-xl text-gray-700">{{ issue.fields.summary }}</h2>
                <!-- <a :href="issue.url" target="_blank" class="flex-grow ml-6 text-gray-300 hover:text-blue-500 duration-150" data-balloon-pos="right" >
                    <svg class="w-6 h-6" fill="none" data-balloon-pos="right" aria-label="Open Card in New Tab" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                </a> -->
                <!-- <span class="text-gray-300 hover:text-gray-600 duration-150 cursor-pointer" @click="clearCard()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </span> -->
            </div>

            <!-- Card Description -->
            <div class="p-8 flex">
                <div class="mr-6 text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                </div>
                <span class="prose" v-html="description"></span>
            </div>

            <div class="grid grid-cols-3 p-8 mt-8">
                <!-- Comments (Left Grid) -->
                <div class="col-span-2">
                    <div v-if="hasComments">
                        <h3 class="uppercase text-gray-600 flex">
                            <svg class="w-6 h-6 mr-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            Comments
                        </h3>

                        <div v-for="(comment, i) in comments" :key="i" class="border-l border-gray-200 ml-3 mb-3">
                            <p class="px-4 py-2 mt-2" v-html="formatComment(comment)"></p>

                            <!-- <p class="ml-8 mt-4">{{ comment.data.text }}</p>
                            <p class="ml-8 text-sm text-gray-400">
                                <span class="ml-4">~ {{ comment.created | dateFjy }}</span>
                            </p>-->
                        </div> 
                    </div>
                    <div class="flex mt-6 ">
                        <input 
                            class="bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-3/4 appearance-none leading-normal" 
                            @keyup.enter="addComment()"
                            placeholder="Add Comment"
                            v-model="commentText"
                        >
                        <div>
                            <button class="px-4 py-2 rounded ml-2 bg-gray-800 text-white focus:outline-none" @click.prevent="addComment()">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Other Data (Right Grid) -->
                
                <div>
                    <!-- <div v-if="issue.labels && issue.labels.length > 0" class="flex uppercase text-gray-600">
                        <svg class="w-6 h-6 mr-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Labels
                    </div>
                    <div>
                        <div v-for="(label, l) in issue.labels" :key="l" class="mt-4 ml-3 text-lg border-l border-gray-200">
                            <p class="ml-8">{{ label.name }}</p>
                        </div>
                    </div>

                    <div v-if="issue.due">
                        <div class="flex text-gray-600 uppercase mt-8">
                            <svg class="w-6 h-6 text-gray-300 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <p>Due Date</p> 
                        </div>
                        <p class="mt-4 ml-3 text-lg border-l border-gray-200">
                            <span class="ml-8">{{ issue.due | dateFjy }}</span>
                        </p>
                    </div> -->

                    <!-- Card Checklists -->
                    <!-- <div v-if="issue.checklists && issue.checklists.length > 0">
                        <h3 class="mt-8 flex text-gray-600 uppercase">
                            <svg class="w-6 h-6 mr-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                            Checklists
                        </h3>
                        <div v-for="(checklist, i) in issue.checklists" :key="i" class="ml-3 border-l border-gray-200">
                            <p class="ml-8 mt-4 text-lg">{{ checklist.name }}</p>
                            <ul class="ml-8 mt-3 list-disc">
                                <li v-for="(checklistItem, j) in checklist.checkItems" :key="j" class="flex">
                                    <span v-if="checklistItem.state != 'incomplete'">
                                        <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </span>
                                    <span v-else>
                                        <svg class="w-6 h-6 text-white mr-2" fill="none"></svg>
                                    </span>
                                    <span :class="(checklistItem.state != 'incomplete') ? 'text-gray-400' : 'text-gray-800'">
                                        {{ checklistItem.name }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div v-if="issue.attachments && issue.attachments.length > 0" class="mt-8">
                        <h3 class="flex text-gray-600 uppercase">
                            <svg class="w-6 h-6 text-gray-300 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            Attachments:
                        </h3>
                        <div v-for="(attachment, a) in issue.attachments" :key="a" class="mt-4 ml-3">
                            <p class="border-l border-gray-200">
                                <a :href="attachment.url" target="_blank" class="text-lg ml-8 text-blue-600 hover:underline">{{ attachment.name }}</a>
                            </p>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    name: 'JiraIssue',

    props: {
        issue: {
            type: Object,
            default: () => {}
        },
        issueId: {
            type: [Number, String],
            default: 0
        }
    },

    data() {
        return {
            issueDetails: {},
            commentText: ''
        }
    },

    computed: {
        hasIssue() {
            return ! _.isEmpty(this.issue)
        },

        hasComments() {
            if (this.issueDetails.fields && this.issueDetails.fields.comment) {
                if (this.issueDetails.fields.comment.comments.length > 0) {
                    return true
                }
            }
            return false
        },

        comments() {
            if (this.hasComments) {
                let array = []
                let commentArray = this.issueDetails.fields.comment.comments
                _.forEach(commentArray, comment => {
                    array.push({
                        content: comment.body.content,
                        date: comment.updated
                    })
                }) 
                return array
            }
            return []
        },

        commentsText() {
            if (this.comments) {
                let textArray = []
                _.forEach(this.comments, comment => {
                    _.forEach(comment, item => {
                        if (item.content) {
                            _.forEach(item.content, content => {
                                textArray.push(content.text)

                            })
                        }

                    })
                })
                return textArray
            }
            return []
        },

        description() {
            let string = ''
            if (this.issueDetails && this.issueDetails.fields && this.issueDetails.fields.description && this.issueDetails.fields.description.content) {
                let content = this.issueDetails.fields.description.content
                _.forEach(content, c => {
                    let innerContent = c.content
                    if (c.type == 'paragraph') {
                        string += '<p>' 
                        _.forEach(innerContent, ic => {
                            if (ic.marks && ic.marks[0].type == 'strong') {
                                string += `<span class="font-bold">${ic.text}</span>`
                            } else {
                                string += (ic.text) ? ic.text : ''
                            }

                        })
                        string += '</p>'
                    } else if (c.type == 'bulletList') {
                        string += '<ul>'
                        _.forEach(innerContent, ic => {
                            string += `<li>${ic.content[0].content[0].text}</li>`
                        })
                        string += '</ul>'
                    }
                })
            }
            return string
        }
    },

    methods: {
        getIssueDetails() {
            axios
                .get('/jira/issue/' + this.issue.id)
                .then(({data}) => {
                    this.issueDetails = data
                })
        },

        formatComment(comment) {
            let string = ''
            _.forEach(comment.content, con => {
                _.forEach(con.content, commentItem => {
                    string += commentItem.text
                })
            })
            return string.replace(/~/g, '<br>~ ')
        },

        addComment() {
            axios
                .post('/jira/comment/' + this.issueDetails.id, {
                    text: this.commentText
                })
                .then(({data}) => {
                    console.log(data)
                    this.commentText = ''
                    this.getIssueDetails()
                })
            console.log('your new comment here')
        },
    },

    mounted() {
        this.getIssueDetails()
    }
}
</script>

<style>

</style>