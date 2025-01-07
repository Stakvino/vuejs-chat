<script setup>
import { AutoComplete, Toast, Badge, Menu, Button, IconField, InputIcon, InputText, Dialog, Message } from 'primevue';
import { ref, onMounted, useTemplateRef, watchEffect, computed } from 'vue';
import { useAppStore } from '@/stores/useApp'
import axios from 'axios';
import ChatChannel from '@/components/ChatChannel/ChatChannel.vue';
import ChatShow from '@/components/ChatShow/ChatShow.vue';
import { initChatBroadcasting } from '@/utils/broadcast';

const { setContentIsReady } = useAppStore();
const chatSearch = ref();

const selectedChannel = ref();
const channels = ref();
const allChannels = computed(() => {
    let allChannels = [];
    for (const type of Object.keys(channels.value)) {
        allChannels = allChannels.concat(channels.value[type]);
    }

    return allChannels;
});

// Update channel last message and time when user send message
const updateChannel = (channelId, updatedChannel) => {
    let channelHasBeenFound = false;
    // Loop over all types of channels (private, public, ...)
    for (const type of Object.keys(channels.value)) {
        channels.value[type].forEach((channel, index) => {
            if (channel.id === channelId) {
                channels.value[type][index] = updatedChannel;
                channelHasBeenFound = true;
                return;
            }
        });
        if (channelHasBeenFound) {
            break;
        }
    }

    // if the channel doesnt exist in the list we should add it
    if ( !channelHasBeenFound ) {
        const type = updatedChannel.type.name;
        channels.value[type].unshift(updatedChannel);
    }

}

const channelsFetchError = ref(false);
onMounted(async () => {
    await axios.get('/api/channels/')
    .then(response => {
        channelsFetchError.value = false;
        channels.value = response.data['channels'];
    })
    .catch(e => {
        channelsFetchError.value = true;
        console.log(e);
    });

    const laravelEcho = initChatBroadcasting();
    for (const channel of allChannels.value) {
        laravelEcho.private(`chat-channel.${channel.id}`)
        .listen('MessageSent', data => {
            // Update messages listing
            const newMessage = {
                ...data.message,
                // 'info': { ...data.messageInfo }
            };
            // Update channels listing
            const updatedChannel = {
                ...data.channel,
                // 'info': { ...data.channelInfo }
            };

            // if user is in the channel that received message and he is scrolled down
            // we will assume he saw the messages... for now
            const isSelectedChannel = selectedChannel.value && channel.id === selectedChannel.value.id;
            const userSawMessage = isSelectedChannel && isScrolledToBottom.value;
            axios.put(`/api/users/message-event-received/${data.channel.id}/${data.message.id}`,
            {
                userSawMessage
            })
            .then(response => {
                if (response.data.success) {
                    newMessage.info = response.data.messageInfo;
                    if ( isSelectedChannel ) {
                        selectedChannel.value.messages.push(newMessage);
                    }
                    updatedChannel.info = response.data.channelInfo;
                    updateChannel(channel.id, updatedChannel);
                    isScrolledToBottom.value = false;
                }
            })
        });
    }

    setContentIsReady(true);
})

// Track the last message sent so that you can load more when the user scroll up
const lastMessage = ref();
const isScrolledToBottom = ref(false);
const onChannelClick = async (event, channelId) => {
    if (selectedChannel.value && channelId === selectedChannel.value.id) return;

    await axios.get(`/api/channels/messages/${channelId}`)
    .then(response => {
        selectedChannel.value = response.data['channel'];
        isScrolledToBottom.value = false;
    });

    // Update unseen messages count to zero when user enter channel chat
    axios.put(`/api/channels/seen/${selectedChannel.value.id}`)
    .then(response => {
        if (response['data']['success']) {
            allChannels.value.forEach(channel => {
                if ( channel.id === selectedChannel.value.id ) {
                    channel.info.unseenMessagesCount = 0;
                }
            });
        }
    });

};

const onChatShowMounted = () => {

}

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
        <div class="h-full border rounded overflow-y-auto overflow-x-hidden flex sm:justify-center">
            <div v-if="channelsFetchError" class="w-full m-2">
                <Message class="w-full" severity="error" icon="pi pi-exclamation-circle">Server error</Message>
            </div>
            <ul v-else class="m-0 list-none border-surface rounded flex flex-col gap-2 w-full sm:w-96 max-w-full">
                <ChatChannel v-if="channels" v-for="channel in channels.public"
                    :channel="channel" :selectedChannel="selectedChannel"
                    :onChannelClick="onChannelClick"
                />
                <ChatChannel v-if="channels" v-for="channel in channels.private"
                    :channel="channel" :selectedChannel="selectedChannel"
                    :onChannelClick="onChannelClick"
                />
            </ul>
            <Toast />
        </div>
       </div>

       <div class="w-full h-full">
            <div class="rounded w-full h-full bg-cover" style="background-image: url('/images/chat/bg.jpg');">
                <div class="w-full h-full pb-2" v-if="selectedChannel">
                    <div class="card rounded-lg h-full flex flex-col">
                        <ChatShow :selectedChannel="selectedChannel"
                            :onChatShowMounted="onChatShowMounted"
                            :isScrolledToBottom="isScrolledToBottom"
                            @chat-scrolled-down="isScrolledToBottom = true"
                        />
                    </div>
                </div>
            </div>
       </div>
    </div>
</template>


<style>
.chat-container {
    height: calc(100vh - 110px);
}
.chat-users-list {
    width: 350px;
    max-height: 100%;
}

</style>
