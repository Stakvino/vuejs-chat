<script setup>
import { AutoComplete, Toast, Badge, Menu, Button, IconField, InputIcon, InputText, Dialog, Message } from 'primevue';
import { ref, onMounted, useTemplateRef, watchEffect, computed } from 'vue';
import { useAppStore } from '@/stores/useApp'
import axios from 'axios';
import ChatChannel from '@/components/ChatChannel/ChatChannel.vue';
import PrivateChatShow from '@/components/ChatShow/PrivateChatShow.vue';
import PublicChatShow from '@/components/ChatShow/PublicChatShow.vue';
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

const channelsFetchError = ref(false);
onMounted(() => {

    axios.get('/api/channels/')
    .then(response => {
        channelsFetchError.value = false;
        channels.value = response.data['channels'];
    })
    .catch(e => {
        channelsFetchError.value = true;
    })
    .then(() => {
        initChatBroadcasting(allChannels.value);
    });

    setContentIsReady(true);
})

// Track the last message sent so that you can load more when the user scroll up
const lastMessage = ref();
const onChannelClick = (event, channelId) => {
    if (selectedChannel.value && channelId === selectedChannel.value.id) return;
    axios.get(`/api/channels/messages/${channelId}`)
    .then(response => {
        selectedChannel.value = response.data['channel'];
    })
    .then(() => {
        // Update unseen messages count to zero when user enter channel chat
        axios.put(`/api/channels/seen/${selectedChannel.value.id}`)
        .then(response => {
            if (response['data']['success']) {
                const channel = allChannels.value.filter(channel => {
                    return channel.id === selectedChannel.value.id;
                })[0];
                channel.unseenMessagesCount = 0;
            }
        })
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
                        <PrivateChatShow v-if="selectedChannel.isPrivate"
                            :selectedChannel="selectedChannel"
                            :onChatShowMounted="onChatShowMounted"
                        />
                        <PublicChatShow v-else :selectedChannel="selectedChannel"
                            :onChatShowMounted="onChatShowMounted"
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
