<script setup>
import { AutoComplete, Button, IconField, InputIcon, InputText, Menu, ProgressSpinner, VirtualScroller } from 'primevue';
import { computed, onMounted, onUpdated, ref, useTemplateRef, watchEffect } from 'vue';
import ChatMessage from '@/components/ChatMessage/ChatMessage.vue';
import axios from 'axios';
import ShowProfile from '@/views/modals/ShowProfile.vue';
import { throttle } from '@/utils/helpers';
import PublicChatHeader from './PublicChatHeader.vue';
import PrivateChatHeader from './PrivateChatHeader.vue';
import { useModalStore } from '@/stores/useModal';
import { storeToRefs } from 'pinia';
import { getLocalMoment } from '../../utils/helpers';

const props = defineProps([
    'selectedChannel', 'isScrolledToBottom', 'messageSentEventUpdate','goToChannel', 'deleteChannel'
]);
const emit = defineEmits(['chat-scrolled-down', 'chat-scrolled-up'])

const showChannelsList = defineModel('showChannelsList');

const modalStore = useModalStore();
const { isProfileModalVisible } = storeToRefs(modalStore);

const showChatScrollDownButton = ref(false);

const scrollChatTo = top => {
    chatMessagesContainer.value.classList.remove("scroll-smooth");
    chatMessagesContainer.value.scrollTo({top: top});
    chatMessagesContainer.value.classList.add("scroll-smooth");
}

const scrollToBottom = () => {
    const target = chatMessagesContainer.value;
    const maxScroll = target.scrollHeight - target.clientHeight;
    scrollChatTo(maxScroll);
    emit('chat-scrolled-down');
    showChatScrollDownButton.value = false;
}

// Scroll chat to bottom everytime user select new chat channel
const chatMessagesContainer = useTemplateRef('chat-messages-container');
onMounted(() => {
    scrollToBottom();
});

const mustChangeChatScroll = ref(false);
const oldChatHeight = ref();
onUpdated(() => {
    if (props.isScrolledToBottom === false && chatMessagesContainer.value) {
        scrollToBottom();
    }

    // Bring back scroll to where it was before we added more messages
    if ( mustChangeChatScroll.value ) {
        const newChatHeight = chatMessagesContainer.value.firstChild.clientHeight;
        if ( newChatHeight >  oldChatHeight.value ) {
            scrollChatTo(newChatHeight - oldChatHeight.value);
        }
        mustChangeChatScroll.value = false
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
const onShowProfile = sender => {
    axios.get(`/api/users/profile/${sender.id}`)
    .then(response => {
        senderProfile.value = response.data['user'];
        isProfileModalVisible.value = true;
    })
}

// Show button that brings user down when they scroll up in chat
const isLoadingMessages = ref(false);
const onChatScroll = throttle(e => {
    const target = e.target;
    const maxScroll = target.scrollHeight - target.clientHeight - 50;
    if (target.scrollTop < maxScroll) {
        showChatScrollDownButton.value = true;
    }else {
        showChatScrollDownButton.value = false;
    }

    // Load more messages when user scroll up
    if ( target.scrollTop < 20 && !isLoadingMessages.value) {
        isLoadingMessages.value = true;
        const oldestMessage = props.selectedChannel.messages[0];
        oldChatHeight.value = chatMessagesContainer.value.firstChild.clientHeight;

        axios.get(
            `/api/channels/messages/${props.selectedChannel.id}`,
            { params: { 'from-date': oldestMessage.created_at } }
        )
        .then(response => {
            const currentMessagesIds = props.selectedChannel.messages.map(message => message.id);
            const newMessages = response.data['channel'].messages
                .filter(message => !currentMessagesIds.find(id => id === message.id));

            props.selectedChannel.messages.unshift(...newMessages);
            isLoadingMessages.value = false;
            mustChangeChatScroll.value = true;
        });
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

const dateDivider = (message, messages) => {
    const messageIndex = messages.indexOf(message);
    if (messageIndex === 0) return true;
    const currentMessageDay = getLocalMoment(message.created_at).day();
    const previousMessageDay = getLocalMoment(messages[messageIndex - 1].created_at).day();
    return currentMessageDay !== previousMessageDay;
}

</script>

<template>
    <div class="h-full flex flex-col">

        <PrivateChatHeader v-if="selectedChannel.isPrivate"
            :selectedChannel="selectedChannel"
            :messageSentEventUpdate="messageSentEventUpdate"
            :goToChannel="goToChannel"
            v-model:showChannelsList="showChannelsList"
        />
        <PublicChatHeader  v-else
            :selectedChannel="selectedChannel"
            :messageSentEventUpdate="messageSentEventUpdate"
            :goToChannel="goToChannel"
            :deleteChannel="deleteChannel"
            v-model:showChannelsList="showChannelsList"
        />

        <div @scroll="onChatScroll" class="chat-messages-container relative p-2 max-h-full border" ref="chat-messages-container" >
            <div class="m-auto md:w-10/12 flex flex-col justify-end items-start">
                <ShowProfile :user="senderProfile" v-model="isProfileModalVisible"
                    :messageSentEventUpdate="messageSentEventUpdate"
                    :goToChannel="goToChannel"
                />
                <div v-if="isLoadingMessages" class="card flex justify-center w-full">
                    <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" fill="white"
                        animationDuration=".5s" aria-label="Custom ProgressSpinner" />
                </div>

                <ChatMessage v-for="message in selectedChannel.messages"
                    v-bind="message.info"
                    :message="message"
                    :mustShowSender="true"
                    :onShowProfile="onShowProfile"
                    :dateDivider="dateDivider(message, selectedChannel.messages)"
                    :class="`chat-message-${message.id}`"
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
