<script setup>
    import { AutoComplete, Toast, Badge, Menu, Button, IconField, InputIcon, InputText } from 'primevue';
    import { ref, onMounted, useTemplateRef } from 'vue';
    import { throttle } from '@/utils/helpers';
    import ChatMessage from '@/components/ChatMessage/ChatMessage.vue';
    import { useAppStore } from '@/stores/useApp'

    const { setContentIsReady } = useAppStore();
    const selectedCountry = ref();
    const filteredCountries = ref();
    const searchInputShow = ref(false);
    const message = ref();
    const chatSearch = ref();
    const showChatScrollDownButton = ref(false)

    const chatMessagesContainer = useTemplateRef('chat-messages-container')
    onMounted(() => {
        const target = chatMessagesContainer.value;
        const maxScroll = target.scrollHeight - target.clientHeight;
        target.scrollTo(0, maxScroll);
        target.classList.add("scroll-smooth")
        setContentIsReady(true);
    })

    const userChatSearch = (event) => {
        setTimeout(() => {
            filteredCountries.value = [{name: "Algeria"}, {name: "Canada"}]
        }, 250);
    }

    const onChatScroll = throttle(e => {
        const target = e.target;
        const maxScroll = target.scrollHeight - target.clientHeight - 50;
        if (target.scrollTop < maxScroll) {
            showChatScrollDownButton.value = true;
        }else {
            showChatScrollDownButton.value = false;
        }
    }, 30)

    const onChatScrollButtonCLick = e => {
        const target = chatMessagesContainer.value;
        const maxScroll = target.scrollHeight - target.clientHeight;
        target.scrollTo(0, maxScroll);
    }

    const selectedUser = ref();
    const users = ref([
        { id: 0, name: 'Amy Elsner', image: 'amyelsner.png', role: 'Admin' },
        { id: 1, name: 'Anna Fali', image: 'annafali.png', role: 'Member' },
        { id: 2, name: 'Asiya Javayant', image: 'asiyajavayant.png', role: 'Member' },
        { id: 3, name: 'Bernardo Dominic', image: 'bernardodominic.png', role: 'Guest' },
        { id: 4, name: 'Elwin Sharvill', image: 'elwinsharvill.png', role: 'Member' },
        { id: 5, name: 'Amy Elsner', image: 'amyelsner.png', role: 'Admin' },
        { id: 6, name: 'Anna Fali', image: 'annafali.png', role: 'Member' },
        { id: 7, name: 'Asiya Javayant', image: 'asiyajavayant.png', role: 'Member' },
        { id: 8, name: 'Bernardo Dominic', image: 'bernardodominic.png', role: 'Guest' },
        { id: 9, name: 'Elwin Sharvill', image: 'elwinsharvill.png', role: 'Member' }
    ]);

    const userOptionsMenu = ref();
    const userOptionsMenuItems = ref([
        {
            items: [
                { label: "Options" },
                { label: 'Block user', icon: 'pi pi-ban' },
                { label: 'Delete chat', icon: 'pi pi-trash', }
            ]
        }
    ]);

    const toggleUserOptionsMenu = (event) => {
        userOptionsMenu.value.toggle(event);
    };

    const onChatUserClick = (event, user) => {
        selectedUser.value = user;
    };
</script>

<template>
    <div class="flex justify-start items-start gap-2 my-5 mx-2 chat-container">
       <div class="h-full flex flex-col chat-users-list">
        <div class="mb-2">
            <IconField>
                <InputIcon class="pi pi-search" />
                <InputText class="w-full !p-2 !pl-8" v-model="chatSearch" placeholder="Search in chat" />
            </IconField>
        </div>
        <div class="h-full border rounded overflow-y-scroll overflow-x-hidden flex sm:justify-center">
            <ul class="m-0 list-none border-surface rounded flex flex-col gap-2 w-full sm:w-96">
                <li
                    v-for="user in users"
                    :key="user.id"
                    class="cursor-pointer"
                    :class="['p-2 hover:bg-emphasis rounded border border-transparent transition-all duration-200 flex items-center justify-content-between', { 'border-primary': selectedUser?.id === user.id }]"
                    @contextmenu="onChatUserClick($event, user)"
                >
                    <div class="flex flex-1 items-center gap-2 w-8/12">
                        <img :alt="user.name" :src="`https://primefaces.org/cdn/primevue/images/avatar/${user.image}`" class="w-8 h-8" />
                        <div class="flex flex-col">
                            <span class="font-bold">{{ user.name }}</span>
                            <p class="text-sm truncate w-10/12">You have a new message from this user</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end h-full">
                        <div class="message-date text-xs">
                            01:58 PM
                        </div>
                        <div class="new-messages-count">
                            <Badge value="2" severity="secondary" size="small"></Badge>
                        </div>
                    </div>
                </li>
            </ul>
            <Toast />
        </div>
       </div>

       <div class="w-full h-full">
            <div class="card rounded-lg h-full flex flex-col">
                <div class="p-2">
                    <div class="flex justify-start items-center flex-1 gap-2">
                        <div class="cursor-pointer min-w-10">
                            <img alt="Username" width="40" src="https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png" />
                        </div>
                        <div class="flex flex-col" v-if="!searchInputShow">
                            <span class="font-bold">Username</span>
                            <span class="text-xs">Last login 11/08/2024</span>
                        </div>
                        <div v-if="searchInputShow" class="chat-search w-full flex justify-center">
                            <AutoComplete class="w-full" v-model="selectedCountry" optionLabel="Search in chat" :suggestions="filteredCountries" @complete="userChatSearch" placeholder="Search in chat">
                                <template #option="slotProps">
                                    <div class="flex items-center">
                                        <img :alt="slotProps.option.name" src="https://primefaces.org/cdn/primevue/images/flag/flag_placeholder.png" :class="`flag flag-trash mr-2`" style="width: 18px" />
                                        <div>{{ slotProps.option.name }}</div>
                                    </div>
                                </template>
                                <template #header></template>
                                <template #footer></template>
                            </AutoComplete>
                        </div>
                        <div class="ml-auto" v-if="searchInputShow" @click="searchInputShow = false">
                            <Button icon="pi pi-times" aria-label="Close search" />
                        </div>
                        <div class="ml-auto" v-if="!searchInputShow" @click="searchInputShow = true">
                            <Button icon="pi pi-search" aria-label="Search in chat" />
                        </div>
                        <div class="">
                            <Button class="three-dots-btn" type="button" icon="pi pi-ellipsis-v" @click="toggleUserOptionsMenu" size="large" aria-haspopup="true" aria-controls="user_options_menu" />
                            <Menu ref="userOptionsMenu" id="user_options_menu" :model="userOptionsMenuItems" :popup="true" />
                        </div>
                    </div>
                </div>

                <div @scroll="onChatScroll" @click="console.log('focued')" class="chat-messages-container relative p-2 overflow-y-scroll overflow-x-hidden max-h-full border" ref="chat-messages-container" >
                    <div class="m-auto md:w-10/12 flex flex-col justify-start items-start">

                        <ChatMessage isMyMessage="true" messageTime="03:36AM" isSeen="">
                            {{ "My message is here not seen yet" }}
                        </ChatMessage>
                        <ChatMessage isMyMessage="true" messageTime="01:36AM" isSeen="true">
                            {{ "My message is here already seen" }}
                        </ChatMessage>

                        <ChatMessage isMyMessage="" messageTime="01:36AM">
                            {{ "Other person message is here" }}
                        </ChatMessage>
                        <ChatMessage isMyMessage="" messageTime="01:36AM">
                            {{ "Other person message is here" }}
                        </ChatMessage>
                        <ChatMessage isMyMessage="" messageTime="01:36AM">
                            {{ "Other person message is here" }}
                        </ChatMessage>
                        <ChatMessage isMyMessage="" messageTime="01:36AM">
                            {{ "Other person message is here" }}
                        </ChatMessage>
                        <ChatMessage isMyMessage="" messageTime="01:36AM">
                            {{ "Other person message is here" }}
                        </ChatMessage>

                    </div>
                    <Button class="chat-scrolldown-button sticky" @click="onChatScrollButtonCLick" v-if="showChatScrollDownButton" severity="secondary" icon="pi pi-arrow-down" raised rounded />

                </div>

                <div class="mt-3 mx-auto md:w-10/12 sending-message-input">
                    <IconField class="w-full">
                        <InputText class="w-full" v-model="message" placeholder="Message" />
                        <InputIcon class="pi pi-send text-xl" />
                    </IconField>
                </div>

            </div>
       </div>
    </div>
</template>


<style>
/* Chat page */
.chat-container {
    height: calc(100vh - 130px);
}
.chat-users-list {
    width: 350px;
    max-height: 100%;
}
.chat-search .p-inputtext {
    padding: 6px 10px;
    width: 100%;
}
.chat-messages-container {
    flex: 1;
}

.my-message-container,
.others-message-container {
    border-radius: 10px;
    padding: 10px;
    border: rgba(200, 200, 200, .5) solid 1px;
    margin-bottom: 15px;
    display: inline;
    max-width: 80%;
}
.my-message-container {
    margin-left: auto;
    background-color: rgba(0, 200, 0, .1);
}
.others-message-container {
    background-color: rgba(150, 150, 150, .1);
}
.message-info {
    color: rgba(100, 100, 100, .5);
}
.message-info.seen {
    color: rgba(0, 200, 0, 1);
}
.sending-message-input .pi-send {
    color: #a881af;
    font-weight: bold;
}
.chat-scrolldown-button {
    left: 100%;
    bottom: 5px;
}

</style>
