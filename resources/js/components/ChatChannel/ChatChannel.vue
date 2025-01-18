<script setup>
import { Badge, Divider } from 'primevue';
import { dateTimeFormat } from '@/utils/helpers';
import { computed } from 'vue';

const props = defineProps([
    'channel', 'onChannelClick', 'selectedChannel', 'unseenMessagesCount', 'showInfo',
    'usersTypingIds'
]);

const formatMessageCount = computed(() => {
    const maxUnseenMessagesCount = 99;
    const count = props.channel.info.unseenMessagesCount;
    return count > maxUnseenMessagesCount ? maxUnseenMessagesCount + "+" : count;
})

</script>

<template>
<li
    :key="channel.id"
    class="p-2 hover:bg-emphasis border-b-4 transition-all duration-200 flex items-start justify-content-between cursor-pointer"
    :class="[{ 'selected-channel': selectedChannel?.id === channel.id }]"
    @click="onChannelClick(channel.id)"
>
    <div :class="channel" class="flex flex-1 items-center gap-2 w-10/12">
        <div class="rounded-full w-8 h-8 bg-cover bg-center"
            :style="
            {
                border: `#5dbea3 solid 1px`,
                backgroundColor: channel.info.receiver.personal_color,
                backgroundImage: `url(${channel.info.receiver.avatar_path})`
            }"
        >
        </div>
        <div class="flex flex-col max-w-full" style="width: calc(100% - 40px);">
            <span class="font-bold">{{ channel.info.receiver.name }}</span>
            <div style="height: 20px;">
                <div v-if="usersTypingIds && usersTypingIds[channel.id] && usersTypingIds[channel.id].length">
                    <img style="background-color: rgba(255,255,255,.5); border-radius: 6px; height: 15px"
                        src="/images/chat/is-typing.gif" width="35" alt="user is typing"
                    >
                </div>
                <p v-else-if="channel.info.lastMessage && showInfo" class="text-sm truncate">{{ channel.info.lastMessage.text }}</p>
            </div>
        </div>
    </div>
    <div class="flex flex-col items-end h-full w-2/12">
        <div v-if="channel.info.lastMessage && showInfo" class="message-date text-xs">
            {{ dateTimeFormat(channel.info.lastMessage.created_at) }}
        </div>
        <div v-if="channel.info.unseenMessagesCount > 0 && showInfo" class="new-messages-count">
            <Badge
                :value="formatMessageCount"
                severity="secondary" size="small"
            >
            </Badge>
        </div>
    </div>
</li>
</template>

<style>
.selected-channel {
    background-color: #a881af !important;
    color: white;
    border-radius: 4px;
}
.selected-channel:hover {
    color: white;
}
</style>
