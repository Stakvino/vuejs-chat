<script setup>
import { Badge, Divider } from 'primevue';
import { dateTimeFormat } from '@/utils/helpers';
import { computed } from 'vue';

const props = defineProps([
    'channel', 'onChannelClick', 'selectedChannel', 'unseenMessagesCount'
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
    class="p-2 hover:bg-emphasis rounded border-b-4 transition-all duration-200 flex items-center justify-content-between cursor-pointer"
    :class="[{ 'selected-channel': selectedChannel?.id === channel.id }]"
    @click="onChannelClick(event, channel.id)"
>
    <div class="flex flex-1 items-center gap-2 w-10/12">
        <div class="rounded-full w-8 h-8 bg-cover"
            :style="
            {
                backgroundColor: channel.info.receiver.personal_color,
                backgroundImage: `url(${channel.info.receiver.avatar_path})`
            }"
        >
        </div>
        <div class="flex flex-col max-w-full" style="width: calc(100% - 40px);">
            <span class="font-bold">{{ channel.info.receiver.name }}</span>
            <p v-if="channel.info.lastMessage" class="text-sm truncate">{{ channel.info.lastMessage.text }}</p>
        </div>
    </div>
    <div class="flex flex-col items-end h-full w-2/12">
        <div v-if="channel.info.lastMessage" class="message-date text-xs">
            {{ dateTimeFormat(channel.info.lastMessage.created_at) }}
        </div>
        <div v-if="channel.info.unseenMessagesCount > 0" class="new-messages-count">
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
    border-radius: 6px;
}
.selected-channel:hover {
    color: white;
}
</style>
