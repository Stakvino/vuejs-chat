<script setup>
import { Badge, Divider } from 'primevue';

const props = defineProps(['channel', 'onChannelClick', 'selectedChannel']);
const formatMessageCount = count => count > 99 ? "99+" : count;

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
                backgroundColor: channel.sender.personal_color,
                backgroundImage: `url(${channel.sender.avatar_path})`
            }"
        >
        </div>
        <div class="flex flex-col max-w-full" style="width: calc(100% - 40px);">
            <span class="font-bold">{{ channel.sender.name }}</span>
            <p class="text-sm truncate">{{ channel.lastMessage.text }}</p>
        </div>
    </div>
    <div class="flex flex-col items-end h-full w-2/12">
        <div class="message-date text-xs">
            {{ channel.lastMessage.since }}
        </div>
        <div v-if="channel.unseenMessagesCount > 0" class="new-messages-count">
            <Badge
                :value="formatMessageCount(channel.unseenMessagesCount)"
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
