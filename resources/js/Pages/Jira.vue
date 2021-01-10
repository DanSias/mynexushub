<template>
    <app-layout>
        <!-- Heading Bar -->
        <template #header>
            <div class="flex items-center justify-between h-18">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex">
                    POLS Analytics Requests
                    <span class="ml-4 text-gray-300 hover:text-gray-500 cursor-pointer" @click="refreshData()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </span>
                </h2>
                <div class="flex">
                    <div v-show="projectName != ''" @click="clearProject()">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex">
                            {{ projectName }}
                            <svg class="ml-2 w-4 h-4 text-gray-300 hover:text-gray-500 duration-150 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </h2>
                    </div>

                    <div>
                        <!-- Open Form -->
                        <button v-if="! showIntakeForm" class="bg-gray-800 hover:bg-gray-700 text-white p-1 ml-12 rounded-full duration-200 focus:outline-none" @click="toggleForm()" data-balloon-pos="left" aria-label="Add New Issue">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </button>
                        <!-- Close Form -->
                        <button v-else class="bg-gray-800 hover:bg-gray-700 text-white p-1 ml-12 rounded-full duration-200 focus:outline-none" @click="toggleForm()" data-balloon-pos="left" aria-label="Close Form">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div v-show="showIntakeForm">
            <JiraForm 
                class="container mx-auto px-8 pb-8"
            />
        </div>
        <div v-show="! showIntakeForm">
            <!-- Projects -->
            <JiraProjects
                :projectId="projectId"
                :projects="projects"
                @setProject="setProject"
            />
            
            <!-- Issues on Current Project -->
            <JiraIssues
                :issueId="issueId"
                :projectId="projectId"
                @setIssue="setIssue"
            />
        </div>
    

        <BaseModal 
            v-if="showModal" 
            :title="(modalType == 'new') ? `POLS Analytics Team Data Request Intake Form Thing` : ''"
            @close="showModal = false" 
            :showFooter="false" 
            scrollable 
        >
            <div v-if="modalType == 'new'">
                
            </div>
            <div v-else>
                <JiraIssue
                    :issueId="issueId"
                    :issue="issue"
                />
            </div>
        </BaseModal>
    </app-layout>
</template>

<script>
import MarkdownItVueLight from 'markdown-it-vue/dist/markdown-it-vue-light.umd.min.js'
import 'markdown-it-vue/dist/markdown-it-vue-light.css'

import AppLayout from './../Layouts/AppLayout'
import JiraProjects from './Jira/Projects'
import JiraIssues from './Jira/Issues'
import JiraIssue from './Jira/Issue'
import JiraForm from './Jira/Form'
import BaseModal from '../Components/BaseModal'

export default {
    name: 'Jira',

    components: {
        AppLayout,
        MarkdownItVueLight,
        JiraProjects,
        JiraIssues,
        JiraIssue,
        JiraForm,
        BaseModal
    },

    data() {
        return {
            projects: [],

            projectId: 0,
            projectKey: '',
            projectName: '',

            issues: [],
            issue: {},
            issueId: 0,

            listId: '',
            listName: '',
            lists: [],

            cards: [],
            cardId: '',
            cardName: '',
            cardData: {},
            commentText: '',

            newCardName: '',
            newCardDesc: '',
            newCardList: '',
            newCardListName: '',

            showModal: false,
            showIntakeForm: true,
            modalType: 'new'
        }
    },

    computed: {
        listIds() {
            return _.map(this.lists, 'id')
        },
        cardListIds() {
            let obj = {}
            _.forEach(this.listIds, id => {
                obj[id] = []
            })
            _.forEach(this.cards, card => {
                obj[card.idList].push(card)
            })
            return obj
        }
    },

    methods: {
        getProjects() {
            axios
                .get('/jira/projects')
                .then(({data}) => this.projects = data.values)
        },

        getIssues() {
            axios
                .get('/jira/issues/' + this.projectId)
                .then(({data}) => this.issues = data)
        },

        toggleForm() {
            // this.showModal = true
            // this.modalType = 'new'
            this.showIntakeForm = ! this.showIntakeForm
        },
        // getBoardCards() {
        //     axios
        //         .get('/trello/cards/' + this.boardId)
        //         .then(({data}) => this.cards = data)
        // },

        setProject(project) {
            this.projectId = project.id
            this.projectKey = project.key
            this.projectName = project.name
            this.getIssues()
        },
        setIssue(issue) {
            this.issue = issue
            this.issueId = issue.id
            this.modalType = 'issue'
            this.showModal=true
        },
        clearProject() {
            this.projectId = 0
            this.projectName = ''
            this.getProjects()
        },

        setCard(card, list) {
            this.cardId = card.id
            this.cardName = card.name
            this.listId = list.id
            this.listName = list.name
            this.getCardData()
        },
        clearIssue() {
            this.cardId = ''
            this.cardName = ''
            this.listId = ''
            this.listName = ''
            this.cardData = {}
        },

        refreshData() {
            if (this.projectId == 0) {
                this.getProjects()
            } else if (this.cardId == '') {
                this.getIssues()
            } else {
                this.getIssues()
            }
            console.log('time to refresh')
        },

        getCardData() {
            axios
                .get('/trello/card/' + this.cardId)
                .then(({data}) => this.cardData = data)
        },


        listCards(listId) {
            return _.filter(this.cards['idList', listId])
        },

        addComment() {
            axios
                .post('/trello/comment/' + this.cardId, {
                    text: this.commentText
                })
                .then(({data}) => {
                    console.log(data)
                    this.commentText = ''
                    this.getCardData()
                })
            console.log('your new comment here')
        },

        addCardModal(list) {
            this.newCardListName = list.name
            this.newCardList = list.id
            this.showModal = true
        },

        addCard() {
            console.log(this.newCardList)
            axios
                .post('/trello/card/' + this.newCardList, {
                    name: this.newCardName,
                    desc: this.newCardDesc
                })
                .then(({data}) => {
                    console.log(data)
                    this.newCardName = ''
                    this.newCardDesc = ''
                    this.getBoardCards()
                    this.showModal = false
                })
            console.log('your new card here')
        }
    },

    mounted() {
        this.getProjects()
    }
}
</script>

<style>

</style>