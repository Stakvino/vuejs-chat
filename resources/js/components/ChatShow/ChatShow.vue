<script setup>
import { AutoComplete, Button, IconField, InputIcon, InputText, Menu, ProgressSpinner, Toast, VirtualScroller } from 'primevue';
import { computed, onMounted, onUpdated, ref, useTemplateRef, watchEffect } from 'vue';
import { useToast } from "primevue/usetoast";
import ChatMessage from '@/components/ChatMessage/ChatMessage.vue';
import axios from 'axios';
import ShowProfile from '@/views/modals/ShowProfile.vue';
import { throttle } from '@/utils/helpers';
import PublicChatHeader from './PublicChatHeader.vue';
import PrivateChatHeader from './PrivateChatHeader.vue';
import { useModalStore } from '@/stores/useModal';
import { storeToRefs } from 'pinia';
import { getLocalMoment } from '../../utils/helpers';
import WaveSurfer from 'wavesurfer.js';
import RecordPlugin from 'wavesurfer.js/dist/plugins/record.js';
import moment from 'moment';

const props = defineProps([
    'selectedChannel', 'isScrolledToBottom', 'messageSentEventUpdate','goToChannel',
    'deleteChannel', 'usersTypingIds'
]);
const emit = defineEmits(['chat-scrolled-down', 'chat-scrolled-up', 'reload-channels-list'])

const showChannelsList = defineModel('showChannelsList');
const toast = useToast();

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
    document.getElementById('messageInput').focus();
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
            axios.post(`/api/messages/user-is-writing/${props.selectedChannel.id}`, {'is-writing': false})
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

let userIsWriting = false;
const onMessageInput = () => {

    if ( !userIsWriting && message.value.trim() ) {
        userIsWriting = true;
        axios.post(`/api/messages/user-is-writing/${props.selectedChannel.id}`, {'is-writing': true})
        // if input has something for 20 seconds remove the "user is writing animation"
        const timeout = 60000;
        setTimeout(() => {
            axios.post(`/api/messages/user-is-writing/${props.selectedChannel.id}`, {'is-writing': false})
        }, timeout);
    }

    if ( !message.value.trim() ) {
        axios.post(`/api/messages/user-is-writing/${props.selectedChannel.id}`, {'is-writing': false})
        .then(response => {
            if (response.data.success) {
                userIsWriting = false;
            }
        });
    }

}

const channelUsersTyping = computed(() => {
    return props.usersTypingIds && props.usersTypingIds[props.selectedChannel.id]
    ? props.usersTypingIds[props.selectedChannel.id]
    : [];
});

const sendFileInput = useTemplateRef('send-file-input');
const attachmentIsLoading = ref(false);
const onattachmentChange = e => {
    if ( !e.target.files.length ) return;
    attachmentIsLoading.value = true;
    const attachment = e.target.files[0];
    const formData = new FormData();
    formData.append('channel_id', props.selectedChannel.id);
    formData.append('text', attachment.name);
    formData.append('attachment', attachment);
    formData.append('_method', 'POST');
    axios.post('/api/messages/', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then(response => {
        if ( response.data['success'] ) {
            const updatedChannel = response.data.channel;
            const newMessage = response.data.message;
            props.messageSentEventUpdate(updatedChannel, newMessage);
        }
    })
    .catch(e => toast.add({ severity: 'error', summary: 'Error while trying to upload file.', life: 3000 }) )
    .finally(() => attachmentIsLoading.value = false)
}

const isRecordingAudio = ref(false);
const audioMessageIsLoading = ref(false);
let audioRecord = null;
const onAudioRecordClick = () => {

    if ( audioRecord && audioRecord.isRecording() ) {
        audioRecord.stopRecording();
        document.getElementById('audioRecordButton').innerHTML = "";
        isRecordingAudio.value = false;
        return;
    }

    const wavesurfer = WaveSurfer.create({
        container: '#audioRecordButton',
        waveColor: 'rgb(168 129 175)',
        progressColor: 'rgb(100, 0, 100)',
        height: 40,
        barHeight: 3
    });
    audioRecord = wavesurfer.registerPlugin(
        RecordPlugin.create({
            renderRecordedAudio: false,
            scrollingWaveform: true,
            continuousWaveform: false,
            continuousWaveformDuration: 30, // optional
        }),
    )

    audioRecord.startRecording()
    .then(() => isRecordingAudio.value = true)
    .then(() => onChatScrollButtonCLick())

    const maxSeconds = 600;
    audioRecord.on('record-progress', (blob) => {
        if ( audioRecord.getDuration() > maxSeconds * 1000 ) {
            audioRecord.stopRecording();
        }
    })

    audioRecord.on('record-end', (blob) => {
        audioMessageIsLoading.value = true;
        const formData = new FormData();
        formData.append('channel_id', props.selectedChannel.id);
        const duration = moment.utc(audioRecord.getDuration()).format('mm:ss');
        formData.append('text', `Audio message - ${duration}`);
        formData.append('audio', blob);
        formData.append('audio-duration', audioRecord.getDuration());
        formData.append('_method', 'POST');
        axios.post('/api/messages/', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        }).then(response => {
            if ( response.data['success'] ) {
                const updatedChannel = response.data.channel;
                const newMessage = response.data.message;
                props.messageSentEventUpdate(updatedChannel, newMessage);
            }
        })
        .catch(e => toast.add({ severity: 'error', summary: 'Error while trying to upload file.', life: 3000 }) )
        .finally(() => audioMessageIsLoading.value = false)

    });

}

</script>

<template>
    <div class="h-full flex flex-col">

        <PrivateChatHeader v-if="selectedChannel.isPrivate"
            :selectedChannel="selectedChannel"
            :messageSentEventUpdate="messageSentEventUpdate"
            :goToChannel="goToChannel"
            v-model:showChannelsList="showChannelsList"
            @reload-channels-list="emit('reload-channels-list')"
        />
        <PublicChatHeader  v-else
            :selectedChannel="selectedChannel"
            :messageSentEventUpdate="messageSentEventUpdate"
            :goToChannel="goToChannel"
            :deleteChannel="deleteChannel"
            v-model:showChannelsList="showChannelsList"
        />

        <div @scroll="onChatScroll" class="chat-messages-container relative py-2 md:p-2 max-h-full border flex flex-col justify-start items-start" ref="chat-messages-container" >
            <div class="mx-auto w-10/12 flex flex-col justify-end items-start">
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

            <div id="audioRecordButton" style="border: #a881af solid 3px"
                :class="{hidden: !isRecordingAudio}"
                class="mt-auto w-8/12 ml-auto bg-white rounded-3xl border">
            </div>

            <div class="flex justify-start items-end">
                <div style="opacity: 0; width: 22px; height: 22px"></div>
                <div v-for="userId in channelUsersTyping">
                    <div v-if="selectedChannel.receivers.find(receiver => receiver.id === userId)"
                            class="relative sender-avatar rounded-full bg-cover bg-center"
                            style="border: #5dbea3 solid 1px; width: 22px; height: 22px"
                            :style="
                            {
                                left: `${channelUsersTyping.indexOf(userId) * -8}px`,
                                backgroundColor: selectedChannel.receivers.find(receiver => receiver.id === userId).personal_color,
                                backgroundImage: `url(${selectedChannel.receivers.find(receiver => receiver.id === userId).avatar_path})`
                            }"
                        >
                    </div>
                </div>
                <img :style="{ left: `${(channelUsersTyping.length) * -8}px` }"
                    v-if="channelUsersTyping.length" class="relative"
                    style="background-color: rgba(255,255,255,.5); border-radius: 6px; height: 15px"
                    src="/images/chat/is-typing.gif" width="35" alt="user is typing"
                >
            </div>

            <!-- <Button class="chat-scrolldown-button sticky" @click="onChatScrollButtonCLick" v-if="showChatScrollDownButton" severity="secondary" icon="pi pi-arrow-down" raised rounded /> -->

        </div>



        <div class="flex mt-2 mx-auto w-11/12 md:w-10/12 sending-message-input relative">

            <Button class="chat-scrolldown-button absolute" @click="onChatScrollButtonCLick" v-if="showChatScrollDownButton" severity="secondary" icon="pi pi-arrow-down" raised rounded />

            <form @submit.prevent="onMessageSubmit" class="flex-1">
                <IconField class="w-full">
                    <InputText id="messageInput" @input="onMessageInput" class="w-full" v-model="message" placeholder="Message" />
                    <InputIcon class="pi pi-send text-xl" />
                </IconField>
            </form>

            <div class="flex">
                <Button
                    class="action-button ml-2"
                    @click="sendFileInput.click()"
                    raised rounded icon="pi pi-paperclip" title="Send a file"
                    :loading="attachmentIsLoading"
                />
                <input class="hidden" type="file" ref="send-file-input" @change="onattachmentChange" >
                <Button class="action-button ml-1 flex justif-center items-center" raised rounded
                    @click="onAudioRecordClick"
                    title="Send a voice message"
                    style="width: 40px; height: 40px"
                    :loading="audioMessageIsLoading"
                >
                    <span v-if="isRecordingAudio" class="flex justify-center items-center" style="width: 40px; height: 40px">
                        <span class="bg-red-600 rounded-full record-red-icon"></span>
                    </span>
                    <i v-else-if="!audioMessageIsLoading" class="pi pi-microphone" style="font-size: 1.2rem"></i>
                </Button>
            </div>

        </div>

        <Toast />
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
    /*left: 100%;
    bottom: 5px;*/
    bottom: calc(100% + 30px);
    right: -30px;
}
@media only screen and (max-width: 750px) {
    .chat-scrolldown-button {
        left: auto;
        right: 0;
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
}

.record-red-icon {
    width: 12px;
    height: 12px;
    /* transition: width 2s, height 2s; */
    animation-name: signal;
    animation-duration: 2s;
    animation-iteration-count: infinite;
}
@keyframes signal {
    0% {background-color: rgba(200,0,0, 1);}
    50% {background-color: rgba(200,0,0, .5);}
    100% {background-color: rgba(200,0,0, 1);}
}

</style>
