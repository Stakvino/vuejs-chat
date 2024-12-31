<script setup>
import { AutoComplete, Button, IconField, InputIcon, InputText, Menu } from 'primevue';
import { onMounted, ref, useTemplateRef, watchEffect } from 'vue';
import ChatMessage from '@/components/ChatMessage/ChatMessage.vue';
import axios from 'axios';
import ShowProfile from '@/views/modals/ShowProfile.vue';
import { throttle } from '@/utils/helpers';

const props = defineProps(['selectedChannel', 'onChatShowMounted']);

const showChatScrollDownButton = ref(false);
const searchInputShow = ref(false);
const selectedCountry = ref();
const filteredCountries = ref();
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

const userChatSearch = (event) => {
    setTimeout(() => {
        filteredCountries.value = [{name: "Algeria"}, {name: "Canada"}]
    }, 250);
}

// Scroll chat to bottom everytime user select new chat channel
const chatMessagesContainer = useTemplateRef('chat-messages-container');
watchEffect(() => {
    if (props.selectedChannel.value && chatMessagesContainer.value) {
        const target = chatMessagesContainer.value;
        const maxScroll = target.scrollHeight - target.clientHeight;
        target.scrollTo(0, maxScroll);
        target.classList.add("scroll-smooth");
    }
})

onMounted(() => {
    const target = chatMessagesContainer.value;
    const maxScroll = target.scrollHeight - target.clientHeight;
    target.scrollTo(0, maxScroll);
    target.classList.add("scroll-smooth");
    props.onChatShowMounted();
});

// Clicking this button will scroll the chat back to the bottom
const onChatScrollButtonCLick = e => {
    const target = chatMessagesContainer.value;
    const maxScroll = target.scrollHeight - target.clientHeight;
    target.scrollTo(0, maxScroll);
}

// The user that can send messages to auth in this channel
const sender = props.selectedChannel.senders[0];
const senderProfile = ref();
const showProfileIsVisible = ref(false);
const onShowProfile = () => {
    axios.get(`/api/users/profile/${sender.id}`)
    .then(response => {
        senderProfile.value = response.data['user'];
        showProfileIsVisible.value = true;
    })
}

// Show button that brings user down when they scroll up in chat
const onChatScroll = throttle(e => {
    const target = e.target;
    const maxScroll = target.scrollHeight - target.clientHeight - 50;
    if (target.scrollTop < maxScroll) {
        showChatScrollDownButton.value = true;
    }else {
        showChatScrollDownButton.value = false;
    }
}, 30);

const message = ref();
</script>

<template>

    <div class="h-full flex flex-col">
        <div class="bg-white p-2">
            <div class="flex justify-start items-center flex-1 gap-2">
                <ShowProfile :user="senderProfile" v-model="showProfileIsVisible" />
                <span class="flex justify-start items-center gap-2" @click="onShowProfile">
                    <div class="cursor-pointer min-w-10">
                        <div class="rounded-full w-12 h-12 bg-cover"
                            :style="{
                            backgroundColor: sender.personal_color,
                            backgroundImage: `url(${sender.avatar_path})`            }"
                        >
                        </div>
                    </div>
                    <div class="flex flex-col" v-if="!searchInputShow">
                        <span class="font-bold">{{ sender.name }}</span>
                        <span class="text-xs">Last login 11/08/2024</span>
                    </div>
                </span>
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
                    <Button style="color: black;" icon="pi pi-times" aria-label="Close search" />
                </div>
                <div class="ml-auto" v-if="!searchInputShow" @click="searchInputShow = true">
                    <Button style="color: black;" icon="pi pi-search" aria-label="Search in chat" />
                </div>
                <div class="">
                    <Button class="three-dots-btn" type="button" icon="pi pi-ellipsis-v" @click="toggleUserOptionsMenu" size="large" aria-haspopup="true" aria-controls="user_options_menu" />
                    <Menu ref="userOptionsMenu" id="user_options_menu" :model="userOptionsMenuItems" :popup="true" />
                </div>
            </div>
        </div>

        <div @scroll="onChatScroll" class="chat-messages-container relative p-2 max-h-full border" ref="chat-messages-container" >
            <div class="m-auto md:w-10/12 flex flex-col justify-end items-start">

                <ChatMessage v-for="message in selectedChannel.messages"
                    :isMyMessage="message.isMyMessage"
                    :messageTime="message.format_created_at"
                    :sender="message.sender"
                    :usersSeen="message.usersSeen"
                >
                    {{ message.text }}
                </ChatMessage>

            </div>
            <Button class="chat-scrolldown-button sticky" @click="onChatScrollButtonCLick" v-if="showChatScrollDownButton" severity="secondary" icon="pi pi-arrow-down" raised rounded />

        </div>

        <div class="mt-2 mx-auto md:w-10/12 sending-message-input bg-white">
            <IconField class="w-full">
                <InputText class="w-full" v-model="message" placeholder="Message" />
                <InputIcon class="pi pi-send text-xl" />
            </IconField>
        </div>
    </div>
</template>

<style>
.chat-search .p-inputtext {
    padding: 6px 10px;
    width: 100%;
}
.sending-message-input {
    border-radius: 15px;
}
.sending-message-input input {
    border-radius: 15px;
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
