<script setup>
import { ref } from 'vue';
import ShowProfile from '@/views/modals/ShowProfile.vue';

const props = defineProps(["messageTime", "usersSeen", "isMyMessage", "sender", "mustShowSender", "onShowProfile"])
const isSeen = props.usersSeen.length;

// if there is more than 2 subscribers in channel, show a message with a list of users
// that saw the message when you hover the message time section
const showSeenMessage = isSeen > 1;
let seenByMessage = "";
if (showSeenMessage) {
    const maxUsersCount = 4;
    seenByMessage = props.usersSeen.slice(0, maxUsersCount)
                    .map(userSeen => `${userSeen.name} - ${userSeen.seen_created_at}`)
                    .join("\n------\n");
    if (props.usersSeen.length > maxUsersCount) {
        seenByMessage += "\n...";
    }
}
</script>

<template>
    <div class="flex justify-start items-start w-full" :class="isMyMessage ? 'flex-row-reverse' : null">
        <div v-if="mustShowSender"
            class="sender-avatar mx-1 mt-1"
            :style="{backgroundColor: sender.personal_color}"
            @click="onShowProfile(sender)"
            v-tooltip.left="sender.name"
        >
            <div class="rounded-full w-full h-full bg-cover"
                style="border: rgba(200,200,200,.9) solid 1px"
                :style="{
                    backgroundColor: sender.personal_color,
                    backgroundImage: `url(${sender.avatar_path})`
                }"
            >
            </div>
        </div>
        <div :class="isMyMessage ? 'my-message-container': 'others-message-container'">
            <span>
                <slot></slot>
            </span>
            <span v-tooltip.top="isSeen ?
                {
                    value: seenByMessage,
                    class: 'users-seen-tooltip'
                }
                : null"
                class="message-info" :class="{seen: isSeen}"
            >
                <span class="ml-3 message-time text-xs">{{ messageTime }}</span>
                <span v-if="isMyMessage">
                    <i class="pi pi-check text-xs seen-icon"></i>
                    <i v-if="isSeen" class="pi pi-check text-xs seen-icon relative" style="right: 10px"></i>
                </span>
            </span>
        </div>
    </div>
</template>

<style>
.sender-avatar {
    border-radius: 99.99%;
    width: 38px;
    height: 38px;
}
.chat-messages-container {
    height: calc( 100% - 60px);
    overflow-y: auto;
    overflow-x: hidden;
}

.my-message-container,
.others-message-container {
    border-radius: 20px;
    padding: 8px 15px;
    border: rgba(200, 200, 200, .5) solid 1px;
    margin-bottom: 15px;
    display: inline;
    max-width: 90%;
    /* min-width: 40%; */
}
.my-message-container {
    margin-left: auto;
    background-color: rgba(168, 129, 175, 1);
    background-color: rgba(50, 50, 50, .8);
    color: white;
}
.others-message-container {
    background-color: rgba(250, 220, 220, .5);
    background-color: white;
}
.message-info {
    color: rgba(100, 100, 100, .6);
}
.my-message-container .message-info {
    color: white;
}
.message-info.seen {
    color: rgba(0, 200, 0, 1);
}
.seen-icon {
    position: relative;
    top: 2px;
    margin-left: 3px;
}
.users-seen-tooltip {
    font-size: 14px;
}
</style>
