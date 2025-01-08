<script setup>
import { AutoComplete, Button, IconField, InputIcon, InputText, Menu } from 'primevue';
import { computed, onMounted, onUpdated, ref, useTemplateRef, watchEffect } from 'vue';
import ChatMessage from '@/components/ChatMessage/ChatMessage.vue';
import axios from 'axios';
import ShowProfile from '@/views/modals/ShowProfile.vue';
import { throttle } from '@/utils/helpers';
import PublicChatHeader from './PublicChatHeader.vue';
import PrivateChatHeader from './PrivateChatHeader.vue';

const props = defineProps(['selectedChannel', 'onChatShowMounted', 'isScrolledToBottom', 'messageSentEventUpdate']);
const emit = defineEmits(['chat-scrolled-down', 'chat-scrolled-up'])

const showChatScrollDownButton = ref(false);

const scrollToBottom = () => {
    const target = chatMessagesContainer.value;
    const maxScroll = target.scrollHeight - target.clientHeight;
    target.classList.remove("scroll-smooth");
    target.scrollTo(0, maxScroll);
    target.classList.add("scroll-smooth");
    emit('chat-scrolled-down');
}

// Scroll chat to bottom everytime user select new chat channel
const chatMessagesContainer = useTemplateRef('chat-messages-container');
onMounted(() => {
    scrollToBottom();
    props.onChatShowMounted();
});
onUpdated(() => {
    if (props.isScrolledToBottom === false && chatMessagesContainer.value) {
        scrollToBottom();
    }
})

// Clicking this button will scroll the chat back to the bottom
const onChatScrollButtonCLick = e => {
    const target = chatMessagesContainer.value;
    const maxScroll = target.scrollHeight - target.clientHeight;
    target.scrollTo(0, maxScroll);
}

// All users in channel except signed user
const receivers = props.selectedChannel.receivers;
// Show user's profile when you click on hes avatar on the chat
const senderProfile = ref();
const showProfileIsVisible = ref(false);
const onShowProfile = sender => {
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
const onMessageSubmit = () => {
    const channelId = props.selectedChannel.id;
    axios.post('/api/messages', { 'text': message.value, 'channel_id': channelId })
    .then(response => {
        if ( response.data['success'] ) {
            const updatedChannel = response.data.channel;
            const newMessage = response.data.message;
            props.messageSentEventUpdate(updatedChannel, newMessage);
            message.value = '';
        }
    });
}

</script>

<template>
    <div class="h-full flex flex-col">

        <PrivateChatHeader v-if="selectedChannel.isPrivate"
            :selectedChannel="selectedChannel"
        />
        <PublicChatHeader  v-else
            :selectedChannel="selectedChannel"
        />

        <div @scroll="onChatScroll" class="chat-messages-container relative p-2 max-h-full border" ref="chat-messages-container" >
            <div class="m-auto md:w-10/12 flex flex-col justify-end items-start">
                <ShowProfile :user="senderProfile" v-model="showProfileIsVisible" :messageSentEventUpdate="messageSentEventUpdate"  />
                <ChatMessage v-for="message in selectedChannel.messages"
                    v-bind="message.info"
                    :message="message"
                    :mustShowSender="true"
                    :onShowProfile="onShowProfile"
                />

            </div>
            <Button class="chat-scrolldown-button sticky" @click="onChatScrollButtonCLick" v-if="showChatScrollDownButton" severity="secondary" icon="pi pi-arrow-down" raised rounded />

        </div>

        <div class="mt-2 mx-auto w-11/12 md:w-10/12 sending-message-input bg-white">
            <form @submit.prevent="onMessageSubmit">
                <IconField class="w-full">
                    <InputText autofocus class="w-full" v-model="message" placeholder="Message" />
                    <InputIcon class="pi pi-send text-xl" />
                </IconField>
            </form>
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
