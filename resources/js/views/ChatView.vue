<script setup>
import { AutoComplete, Toast, Badge, Menu, Button, IconField, InputIcon, InputText, Dialog, Message, Tabs, TabList, Tab, TabPanels, TabPanel, Divider, ProgressSpinner } from 'primevue';
import { ref, onMounted, useTemplateRef, watchEffect, computed } from 'vue';
import { useAppStore } from '@/stores/useApp'
import axios from 'axios';
import ChatChannel from '@/components/ChatChannel/ChatChannel.vue';
import ChatShow from '@/components/ChatShow/ChatShow.vue';
import { initChatBroadcasting } from '@/utils/broadcast';
import { useAuthStore } from '@/stores/useAuth';
import { storeToRefs } from 'pinia';
import { useModalStore } from '@/stores/useModal';
import ShowProfile from '@/views/modals/ShowProfile.vue';
import { dateTimeFormat } from '@/utils/helpers';
import { throttle } from '@/utils/helpers';
import { debounce } from '@/utils/helpers';
import ShowChannel from '@/views/modals/ShowChannel.vue';

const authStore = useAuthStore();
const { authUser } = storeToRefs(authStore);
const { setContentIsReady } = useAppStore();

const appStore = useAppStore();
const { isMobileScreen } = storeToRefs(appStore);

const modalStore = useModalStore();
const { isProfileModalVisible, isChannelModalVisible } = storeToRefs(modalStore);

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
const updateChannel = (updatedChannel) => {
    let channelHasBeenFound = false;
    // Loop over all types of channels (private, public, ...)
    for (const type of Object.keys(channels.value)) {
        channels.value[type].forEach((channel, index) => {
            if (channel.id === updatedChannel.id) {
                channels.value[type].splice(index, 1);
                channels.value[type].unshift(updatedChannel);
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

const deleteChannel = channelId => {
    // Loop over all types of channels (private, public, ...)
    for (const type of Object.keys(channels.value)) {
        channels.value[type] = channels.value[type].filter(channel => channel.id !== channelId);
    }
    selectedChannel.value = null;
}

// Update channel last message and time when user send message
const updateMessages = (newMessage) => {
    // Check if message has already been added
    const messageAlreadyAdded = selectedChannel.value.messages.find(message => message.id === newMessage.id);
    if ( !messageAlreadyAdded ) {
        selectedChannel.value.messages.push(newMessage);
    }
    else {
        // Replace old message with new if you have to ?
    }
}

// Update the channel list and the messages listing after a MessageSent event is dispatched
const messageSentEventUpdate = (updatedChannel, newMessage) => {
    const isSelectedChannel = selectedChannel.value
        && ( updatedChannel.id === selectedChannel.value.id )
        ? true : false;

    if ( isSelectedChannel ) {
        updateMessages(newMessage);
    }
    updateChannel(updatedChannel);
    isScrolledToBottom.value = false;

}

const channelsFetchError = ref(false);
const usersList = ref();
const blockedUsers = ref();
const allPublicChannels = ref();

const loadUserChannels = async () => {
    return axios.get('/api/channels/')
    .then(response => {
        channelsFetchError.value = false;
        channels.value = response.data['channels'];
    })
    .catch(e => {
        channelsFetchError.value = true;
        console.log(e);
    });
}

const usersTypingIds = ref({});
onMounted(async () => {
    await loadUserChannels();

    const laravelEcho = initChatBroadcasting();
    // Listen to users sending messages
    laravelEcho.private(`chat-channel.${authUser.value.id}`)
    .listen('MessageSent', async data => {
        const newMessage = data.message;
        const updatedChannel = data.channel;

        // if user is in the channel that received message and he is scrolled down
        // we will assume he saw the messages... for now
        const isSelectedChannel = selectedChannel.value && ( updatedChannel.id === selectedChannel.value.id )
            ? true : false;

        const userSawMessage = isSelectedChannel && isScrolledToBottom.value;
        axios.put(
            `/api/users/message-event-received/${data.channel.id}/${data.message.id}`
            , {userSawMessage: userSawMessage}
        )
        .then(response => {
            if (response.data.success) {
                newMessage.info = response.data.messageInfo;
                updatedChannel.info = response.data.channelInfo;
                messageSentEventUpdate(updatedChannel, newMessage);
                if (!isSelectedChannel && newMessage.info.sender.id !== authUser.value.id) {
                    messageReceivedAudio.value.play();
                }
            }
        })
    });

    // Listen to users seeing your messages
    laravelEcho.private(`message-seen.${authUser.value.id}`)
    .listen('MessageSeen', async data => {
        const channel = data.channel;
        const updatedMessagesIds = data.messagesIds;
        const updatedMessages = await axios.get(
            '/api/messages/get-messages'
            , { params: {'messages-ids': updatedMessagesIds} }
        )
        .then(response => response.data);

        const channelIsSelected = selectedChannel.value && channel.id === selectedChannel.value.id;
        if ( channelIsSelected ) {
            let lastMessageUpdated = null;
            selectedChannel.value.messages = selectedChannel.value.messages.map(message => {
                const messageHasBeenUpdated = updatedMessages.find(updatedMessage => message.id === updatedMessage.id );
                // if message has been updated, replace old message with updatedMessage
                if ( messageHasBeenUpdated ) {
                    const index = updatedMessages.findIndex(updatedMessage => message.id === updatedMessage.id )
                    lastMessageUpdated = updatedMessages[index];
                    return updatedMessages[index];
                }
                // otherwise just return the message as it is
                return message;
            });

            const messageWasSentByAuth = lastMessageUpdated && lastMessageUpdated.info.sender.id === authUser.value.id;
            const messageWasSeenByOthers = lastMessageUpdated && lastMessageUpdated.info.usersSeen.length;
            if ( messageSeenAudio.value && messageWasSentByAuth && messageWasSeenByOthers && channelIsSelected ) {
                messageSeenAudio.value.play();
            }

        }
    });

    // Listen to users sending messages
    laravelEcho.private(`user-writing.${authUser.value.id}`)
    .listen('UserWriting', data => {
        // if its me typing you dont have to show it
        if ( authUser.value.id === data.userTypingId ) return;

        // Add channel if its not there
        if ( !usersTypingIds.value[data.channel.id] ) {
            usersTypingIds.value[data.channel.id] = [];
        }

        const channelUsersIds = usersTypingIds.value[data.channel.id];
        // User started writing
        if ( data.isWriting && channelUsersIds.indexOf(data.userTypingId) === -1 ) {
            usersTypingIds.value[data.channel.id].unshift(data.userTypingId);
            const channelIsSelected = selectedChannel.value && data.channel.id === selectedChannel.value.id;
            if ( channelIsSelected ) {
                isWritingAudio.value.play();
            }
        }
        // User stoped writing
        if ( !data.isWriting ) {
            const index = channelUsersIds.indexOf(data.userTypingId);
            usersTypingIds.value[data.channel.id].splice(index, 1);
        }

        const maxUsersToShow = 10;
        usersTypingIds.value[data.channel.id] = channelUsersIds.slice(0, maxUsersToShow)
    })

    setContentIsReady(true);
})

// Track the last message sent so that you can load more when the user scroll up
const lastMessage = ref();
const isScrolledToBottom = ref(false);
const isChatShowReady = ref(true);
const goToChannel = async channelId => {
    isChatShowReady.value = false;
    if (selectedChannel.value && channelId === selectedChannel.value.id) {
        isChatShowReady.value = true;
        return;
    }
    // Get messages of selected channel
    await axios.get(`/api/channels/messages/${channelId}`)
    .then(response => {
        selectedChannel.value = response.data['channel'];
        isScrolledToBottom.value = false;
        selectedTab.value = "0";
        isChatShowReady.value = true;
    });

    // Update unseen messages count to zero when user enter channel chat
    axios.put(`/api/channels/seen/${selectedChannel.value.id}`, {
        'messagesIds':  selectedChannel.value.messages.map(message => message.id)
    })
    .then(response => {
        if (response['data']['success']) {
            allChannels.value.forEach(channel => {
                if ( channel.id === selectedChannel.value.id ) {
                    channel.info.unseenMessagesCount = 0;
                }
            });
        }
    });

    const messageInput = document.getElementById('messageInput');
    if ( messageInput ) messageInput.focus();
}

const onChannelClick = (channelId) => {
    goToChannel(channelId);
    showChannelsList.value = false;
};

const showChannelsList = ref(true);

const selectedUser = ref();
const onShowProfile = user => {
    axios.get(`/api/users/profile/${user.id}`)
    .then(response => {
        selectedUser.value = response.data['user'];
        isProfileModalVisible.value = true;
    })
}

const fetchUserslist = () => {
    axios.get('/api/users/all')
    .then(response => {
        usersList.value = response.data.users;
        blockedUsers.value = response.data['blocked-users'];
    });
}

const fetchPublicChannels = () => {
    axios.get('/api/channels/public')
    .then(response => {
        allPublicChannels.value = response.data.channels;
    });
}

const selectedTab = ref("0");
const selectedUserTab = ref('0');
const userSearch = ref();
const onUserSearchInput = debounce(
    () => {
        axios.get('/api/users/all', { params: {"user-search": userSearch.value} })
        .then(response => {
            usersList.value = response.data.users;
        })
    }
);

const channel = ref();
const onShowChannel = channelId => {
    axios.get(`/api/channels/${channelId}`)
    .then(response => {
        channel.value = response.data['channel'];
        isChannelModalVisible.value = true;
    });
}

const userIsSubscribed = channelId => {
    if (!channels.value) return;
    for (const type of Object.keys(channels.value)) {
        if ( channels.value[type].find(channel => channel.id === channelId) ) {
            return true;
        }
    }
    return false;
}


const messageReceivedAudio = useTemplateRef('message-received-audio');
const messageSeenAudio = useTemplateRef('message-seen-audio');
const isWritingAudio = useTemplateRef('is-writing-audio');

</script>

<template>
    <div class="flex justify-start items-start gap-2 my-5 mx-2 chat-container">

        <div v-if="!isChatShowReady && isMobileScreen" class="h-full w-full flex justify-center items-center">
            <ProgressSpinner strokeWidth="4" fill="transparent"
            animationDuration="2.3s" aria-label="Custom ProgressSpinner" />
        </div>

       <div v-if="isMobileScreen && showChannelsList || !isMobileScreen" class="h-full flex flex-col chat-side-container">
            <div class="w-full h-full">
                <Tabs v-model:value="selectedTab">
                    <TabList>
                        <Tab class="w-4/12" value="0" @click="loadUserChannels">
                            <i class="pi pi-inbox"></i> <span class="ml-3">Messages</span>
                        </Tab>
                        <Tab class="w-4/12" value="1" @click="fetchUserslist">
                            <i class="pi pi-user"></i>  <span class="ml-3">Users</span>
                        </Tab>
                        <Tab class="w-4/12" value="2" @click="fetchPublicChannels">
                            <i class="pi pi-globe"></i>  <span class="ml-3">Channels</span>
                        </Tab>
                    </TabList>
                    <TabPanels class="p-1">
                        <TabPanel value="0" style="height: calc(100vh - 150px) ;overflow-y:auto">
                            <div class="w-full h-full border rounded overflow-y-auto overflow-x-hidden flex sm:justify-center">
                                <div v-if="channelsFetchError" class="w-full m-2">
                                    <Message class="w-full" severity="error" icon="pi pi-exclamation-circle">Server error</Message>
                                </div>
                                <!-- sm:w-96 -->
                                <ul v-else class="m-0 list-none border-surface rounded flex flex-col w-full max-w-full">
                                    <ChatChannel v-if="channels" v-for="channel in channels.public"
                                        :channel="channel" :selectedChannel="selectedChannel"
                                        :onChannelClick="onChannelClick"
                                        :goToChannel="goToChannel"
                                        :usersTypingIds="usersTypingIds"
                                        :showInfo="true"
                                    />
                                    <ChatChannel v-if="channels" v-for="channel in channels.private"
                                        :channel="channel" :selectedChannel="selectedChannel"
                                        :onChannelClick="onChannelClick"
                                        :goToChannel="goToChannel"
                                        :usersTypingIds="usersTypingIds"
                                        :showInfo="true"
                                    />
                                </ul>
                                <Toast />
                            </div>
                        </TabPanel>
                        <TabPanel value="1" class="p-2" style="height: calc(100vh - 150px) ;overflow-y:auto">
                            <div class="border-surface rounded">
                                <Tabs v-model:value="selectedUserTab">
                                    <TabList>
                                        <Tab class="w-6/12 secondary" value="0" @click="fetchUserslist">
                                            <i class="pi pi-list"></i> <span class="ml-3">List</span>
                                        </Tab>
                                        <Tab class="w-6/12 danger" value="1" @click="fetchUserslist">
                                            <i class="pi pi-ban"></i> <span class="ml-3">Blocked</span>
                                        </Tab>
                                    </TabList>
                                    <TabPanels class="p-1">
                                        <TabPanel value="0" >
                                            <div class="mt-2">
                                                <InputText class="!py-1 !px-3 w-full mb-2" placeholder="Find a user" type="text" v-model="userSearch" @input="onUserSearchInput" />
                                            </div>
                                            <ShowProfile :user="selectedUser" v-model="isProfileModalVisible"
                                                :messageSentEventUpdate="messageSentEventUpdate"
                                                :goToChannel="goToChannel"
                                                @reload-users-list="fetchUserslist"
                                            />
                                            <div v-for="user of usersList" @click="onShowProfile(user)" class="cursor-pointer hover:bg-emphasis rounded transition-all duration-200">
                                                <div class="flex flex-1 items-center gap-2 w-12/12 p-3">
                                                    <div class="rounded-full w-8 h-8 bg-cover bg-center"
                                                        :style="{
                                                            border: `#5dbea3 solid 1px`,
                                                            backgroundColor: user.personal_color,
                                                            backgroundImage: `url(${user.avatar_path})`
                                                        }"
                                                    >
                                                    </div>
                                                    <div class="flex flex-col flex-1">
                                                        <span class="font-bold">{{ user.name }}</span>
                                                        <p class="text-sm truncate w-10/12">Last login : {{ dateTimeFormat(user.last_login_at) }}</p>
                                                    </div>
                                                </div>
                                                <Divider class="my-0" />
                                            </div>
                                        </TabPanel>
                                        <TabPanel value="1" >
                                            <div v-if="blockedUsers && blockedUsers.length">
                                                <div v-for="user of blockedUsers" @click="onShowProfile(user)" class="cursor-pointer hover:bg-emphasis rounded transition-all duration-200">
                                                    <div class="flex flex-1 items-center gap-2 w-12/12 p-3">
                                                        <div class="rounded-full w-8 h-8 bg-cover bg-center"
                                                            :style="{
                                                                border: `#5dbea3 solid 1px`,
                                                                backgroundColor: user.personal_color,
                                                                backgroundImage: `url(${user.avatar_path})`
                                                            }"
                                                        >
                                                        </div>
                                                        <div class="flex flex-col flex-1">
                                                            <span class="font-bold">{{ user.name }}</span>
                                                            <p class="text-sm truncate w-10/12">Last login : {{ dateTimeFormat(user.last_login_at) }}</p>
                                                        </div>
                                                    </div>
                                                    <Divider class="my-0" />
                                                </div>
                                            </div>
                                            <div v-else>
                                                <Message class="mt-5" severity="info">
                                                    You have not blocked any user yet.
                                                </Message>
                                            </div>
                                        </TabPanel>
                                    </TabPanels>
                                </Tabs>
                            </div>
                        </TabPanel>
                        <TabPanel value="2" class="p-2" style="height: calc(100vh - 150px) ;overflow-y:auto">
                            <ShowChannel :channel="channel"
                                v-model="isChannelModalVisible"
                                :messageSentEventUpdate="messageSentEventUpdate"
                                :goToChannel="goToChannel"
                                :isSubscribed="channel && userIsSubscribed(channel.id)"
                                :loadUserChannels="loadUserChannels"
                            />
                            <ChatChannel v-if="allPublicChannels" v-for="channel in allPublicChannels"
                                :channel="channel" :selectedChannel="selectedChannel"
                                :onChannelClick="() => onShowChannel(channel.id)"
                                :goToChannel="goToChannel"
                                :showInfo="true"
                            />
                            <Message class="mt-5" v-if="allPublicChannels && !allPublicChannels.length" severity="info">
                                You are already subscribed to all public channels.
                            </Message>
                        </TabPanel>
                    </TabPanels>
                </Tabs>
            </div>
       </div>

       <div v-if="isChatShowReady && isMobileScreen && !showChannelsList || !isMobileScreen" class="flex-1 h-full">
            <div class="rounded w-full h-full bg-cover" style="background-image: url('/images/chat/bg.jpg');">
                <div class="w-full h-full pb-2" v-if="selectedChannel">
                    <div class="card rounded-lg h-full flex flex-col">
                        <ChatShow :selectedChannel="selectedChannel"
                            :isScrolledToBottom="isScrolledToBottom"
                            :messageSentEventUpdate="messageSentEventUpdate"
                            @chat-scrolled-down="isScrolledToBottom = true"
                            @reload-channels-list="loadUserChannels"
                            :goToChannel="goToChannel"
                            :deleteChannel="deleteChannel"
                            :usersTypingIds="usersTypingIds"
                            v-model:showChannelsList="showChannelsList"
                        />
                    </div>
                </div>
            </div>
       </div>

       <audio ref="message-received-audio" src="/audio/message-received.wav"></audio>
       <audio class="hidden" ref="message-seen-audio" src="/audio/message-seen.wav"></audio>
       <audio class="hidden" ref="is-writing-audio" src="/audio/is-writing.wav"></audio>
    </div>
</template>


<style>
.chat-container {
    height: calc(100vh - 110px);
}
.chat-side-container {
    width: 350px;
    max-height: 100%;
}

@media only screen and (max-width: 750px) {
    .chat-side-container {
        width: 100%;
    }
}

.chat-side-container .p-tab-active {
    color: white;
    border-radius: 3px;
    padding: 4px;
    background-color: #a881af;
}
.chat-side-container .p-tab-active.secondary {
    background-color: transparent;
    color: #5dbea3;
}
.chat-side-container .p-tab-active.danger {
    background-color: transparent;
    color: red;
}
</style>
