<template>
    <app-layout>
        <!-- Heading Bar -->
        <template #header>
            <div class="flex items-center justify-between h-18">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex">
                    Trello Goodness
                    <span class="ml-4 text-gray-300 hover:text-gray-500 cursor-pointer" @click="refreshData()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </span>
                </h2>
                <div class="flex">
                    <div v-show="boardName != ''" @click="clearBoard()">
                        <div class="flex">
                            {{ boardName }}
                            <svg class="ml-2 w-4 h-4 text-gray-300 hover:text-gray-500 duration-150 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                    </div>
                    <div v-show="listName != ''" @click="clearCard()" class="ml-6">
                        <div class="flex">
                            [{{ listName }}] {{ cardName }}
                            <svg class="ml-2 w-4 h-4 text-gray-300 hover:text-gray-500 duration-150 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Boards -->
        <div v-show="boardName == ''" class="container mx-auto mt-8">
            <div class="grid grid-cols-2 gap-5">
                <div v-for="(board, i) in boards" :key="i">
                    <div class="bg-white p-8 flex cursor-pointer shadow-sm hover:shadow" @click="setBoard(board)">
                        <div class="">
                            <img :src="boardImage(board)" class="h-20 w-20 rounded-full">
                        </div>
                        <div>
                            <h2 class="ml-6 text-xl mt-1 mb-auto">{{ board.name}}</h2>
                            <p class="ml-6 text-sm text-gray-400 truncate">{{ board.desc.substring(0, 70) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lists and Cards in selected board -->
        <div v-show="boardName != '' && cardName == ''" class="container mx-auto mt-8">
            <div class="grid grid-cols-4 -mx-4">
                <div v-for="(list, i) in lists" :key="i" class=" mx-4">
                    <div class="pb-2 text-center text-lg border-b border-gray-300 flex">
                        <div class="flex-grow">{{ list.name }}</div>
                        <div class="text-gray-300 hover:text-gray-600 duration-150 cursor-pointer" @click="addCardModal(list)" data-balloon-pos="left" aria-label="Add Card">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div v-for="(card, j) in cardListIds[list.id]" :key="j" class="my-4 bg-white hover:bg-gray-50 rounded shadow-sm hover:shadow cursor-pointer" @click="setCard(card, list)">
                        <p class="text-left text-sm text-semibold py-3 px-4 border-b border-gray-200 text-gray-700" >
                            {{ card.name }}
                        </p>
                        <!-- <p class="text-sm py-4 px-4 text-gray-500">
                            <markdown-it-vue-light class="prose" :content="card.desc" />
                        </p> -->
                    </div>
                </div>
            </div>
        </div>

        <div v-if="cardName != ''" class="container max-w-4xl mx-auto bg-white  mt-8 rounded shadow-sm hover:shadow ">
            <!-- Card Header -->
            <div class="flex border-b border-gray-200 py-4 px-6">
                <h2 class=" text-xl text-gray-700">{{ cardName }}</h2>
                <a :href="cardData.url" target="_blank" class="flex-grow ml-6 text-gray-300 hover:text-blue-500 duration-150" data-balloon-pos="right" >
                    <svg class="w-6 h-6" fill="none" data-balloon-pos="right" aria-label="Open Card in New Tab" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                </a>
                <span class="text-gray-300 hover:text-gray-600 duration-150 cursor-pointer" @click="clearCard()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </span>
            </div>

            <!-- Card Description -->
            <div class="p-8 flex">
                <div class="mr-6 text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                </div>
                <markdown-it-vue-light class="flex-grow prose" :content="cardData.desc" />
            </div>

            <div class="grid grid-cols-2 p-8 mt-8">
                <!-- Comments (Left Grid) -->
                <div>
                    <div v-if="cardData.actions && cardData.actions.length > 0">
                        <h3 class="uppercase text-gray-600 flex">
                            <svg class="w-6 h-6 mr-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            Comments
                        </h3>
                        <div v-for="(comment, i) in cardData.actions" :key="i" class="border-l border-gray-200">
                            <p class="ml-8 mt-4">{{ comment.data.text }}</p>
                            <p class="ml-8 text-sm text-gray-400">
                                <span class="ml-4">~ {{ comment.date | dateFjy }}</span>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input 
                            class="bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded mt-6 py-2 px-4 block w-3/4 appearance-none leading-normal" 
                            @keyup.enter="addComment()"
                            placeholder="Add Comment"
                            v-model="commentText"
                        >
                    </div>
                </div>
                <!-- Other Data (Right Grid) -->
                <div>
                    <div v-if="cardData.labels && cardData.labels.length > 0" class="flex uppercase text-gray-600">
                        <svg class="w-6 h-6 mr-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Labels
                    </div>
                    <div>
                        <div v-for="(label, l) in cardData.labels" :key="l" class="mt-4 ml-3 text-lg border-l border-gray-200">
                            <p class="ml-8">{{ label.name }}</p>
                        </div>
                    </div>

                    <div v-if="cardData.due">
                        <div class="flex text-gray-600 uppercase mt-8">
                            <svg class="w-6 h-6 text-gray-300 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <p>Due Date</p> 
                        </div>
                        <p class="mt-4 ml-3 text-lg border-l border-gray-200">
                            <span class="ml-8">{{ cardData.due | dateFjy }}</span>
                        </p>
                    </div>

                    <!-- Card Checklists -->
                    <div v-if="cardData.checklists && cardData.checklists.length > 0">
                        <h3 class="mt-8 flex text-gray-600 uppercase">
                            <svg class="w-6 h-6 mr-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                            Checklists
                        </h3>
                        <div v-for="(checklist, i) in cardData.checklists" :key="i" class="ml-3 border-l border-gray-200">
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

                    <div v-if="cardData.attachments && cardData.attachments.length > 0" class="mt-8">
                        <h3 class="flex text-gray-600 uppercase">
                            <svg class="w-6 h-6 text-gray-300 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            Attachments:
                        </h3>
                        <div v-for="(attachment, a) in cardData.attachments" :key="a" class="mt-4 ml-3">
                            <p class="border-l border-gray-200">
                                <a :href="attachment.url" target="_blank" class="text-lg ml-8 text-blue-600 hover:underline">{{ attachment.name }}</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Card Modal -->
        <div v-if="showModal" class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center mt-32 flex">
            <div class="my-6 mx-auto max-w-4xl">
                <!--content-->
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                    <!--header-->
                    <div class="flex items-start justify-between py-4 px-6 border-b border-solid border-gray-300 rounded-t">
                        <h3 class="text-xl font-semibold mx-6">
                            Add Card to List: {{ newCardListName }}
                        </h3>
                        <button class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" @click="showModal = false">
                        <span class="bg-transparent text-gray-300 hover:text-gray-600 duration-150 opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none" style="transition: all .25s ease">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </span>
                        </button>
                    </div>

                    <!--body-->
                    <div class="relative p-6 flex-auto">
                        
                    <input v-model="newCardName" placeholder="Name" class="mb-2 bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal">

                    <textarea v-model="newCardDesc" placeholder="Description" class="bg-white focus:outline-none focus:shadow-outline border border-gray-200 rounded py-2 px-4 block w-full appearance-none leading-normal"></textarea>

                    </div>
                    <!--footer-->
                    <div class="flex items-center justify-end px-6 py-2 border-t border-solid border-gray-300 rounded-b">
                        <button class="text-gray-400 hover:text-gray-700 background-transparent font-bold uppercase px-8 py-2 text-sm outline-none focus:outline-none mr-1 mb-1" type="button" style="transition: all .25s ease" @click="showModal = false">
                            Cancel
                        </button>
                        <button class="text-blue-500 bg-transparent border border-solid border-blue-500 hover:bg-blue-500 hover:text-white active:bg-blue-600 font-bold uppercase text-sm px-4 py-2 rounded outline-none focus:outline-none mr-1 mb-1" type="button" style="transition: all .15s ease" @click="addCard()">
                            Add Card
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="showModal" class="opacity-25 fixed inset-0 z-40 bg-black" @click.self="hideModal()"></div>

    </app-layout>
</template>

<script>
import MarkdownItVueLight from 'markdown-it-vue/dist/markdown-it-vue-light.umd.min.js'
import 'markdown-it-vue/dist/markdown-it-vue-light.css'

import AppLayout from './../Layouts/AppLayout'

export default {
    name: 'Trello',

    components: {
        AppLayout,
        MarkdownItVueLight
    },

    data() {
        return {
            boardId: '',
            boardName: '',
            boards: [],
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

            showModal: false
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
        getBoards() {
            axios
                .get('/trello/boards')
                .then(({data}) => this.boards = data)
        },

        getBoardLists() {
            axios
                .get('/trello/lists/' + this.boardId)
                .then(({data}) => this.lists = data)
        },
        getBoardCards() {
            axios
                .get('/trello/cards/' + this.boardId)
                .then(({data}) => this.cards = data)
        },

        setBoard(board) {
            this.boardId = board.id
            this.boardName = board.name
            this.getBoardLists()
            this.getBoardCards()
        },
        clearBoard() {
            this.boardId = ''
            this.boardName = ''
            this.getBoards()
        },

        setCard(card, list) {
            this.cardId = card.id
            this.cardName = card.name
            this.listId = list.id
            this.listName = list.name
            this.getCardData()
        },
        clearCard() {
            this.cardId = ''
            this.cardName = ''
            this.listId = ''
            this.listName = ''
            this.cardData = {}
        },

        refreshData() {
            if (this.boardId == '') {
                this.getBoards()
            } else if (this.cardId == '') {
                this.getBoardLists()
                this.getBoardCards()
            } else {
                this.getCardData()
            }
            console.log('time to refresh')
        },

        getCardData() {
            axios
                .get('/trello/card/' + this.cardId)
                .then(({data}) => this.cardData = data)
        },

        boardImage(board) {
            if (board.prefs && board.prefs.backgroundImageScaled) {
                return board.prefs.backgroundImageScaled[0].url
            }
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
        this.getBoards()
    }
}
</script>

<style>

</style>