<script setup>
import { computed, onUpdated, ref, watchEffect } from 'vue';
import ShowProfile from '@/views/modals/ShowProfile.vue';
import { dateTimeFormat } from '@/utils/helpers';
import { getLocalMoment } from '@/utils/helpers';
import { useAuthStore } from '@/stores/useAuth';
import { storeToRefs } from 'pinia';
import { Divider } from 'primevue';

const props = defineProps([
    "message", "usersSeen", "isMyMessage", "sender", "mustShowSender", "onShowProfile",
    "dateDivider"
])

const authStore = useAuthStore();
const { authUser } = storeToRefs(authStore);

const isSeen = computed(() => props.usersSeen.length);

// if there is more than 2 subscribers in channel, show a message with a list of users
// that saw the message when you hover the message time section
const showSeenMessage = computed(() => isSeen.value >= 1);
const seenByMessage = ref("");
const computedSeenByMessage = computed(() => {
    if (showSeenMessage.value) {
        const maxUsersCount = 4;
        seenByMessage.value = props.usersSeen.slice(0, maxUsersCount)
            .map(userSeen => {
                const dateTime = getLocalMoment(userSeen.seen_created_at).format('DD/MMM/YY HH:mm');
                return `${userSeen.name} - ${dateTime}`;
            })
            .join("\n------\n");
        if (props.usersSeen.length > maxUsersCount) {
            seenByMessage.value += "\n...";
        }
    }
    return seenByMessage.value;
});

</script>

<template>
    <div class="chat-message-container w-full">
        <Divider v-if="dateDivider" align="center" type="dotted">
            <div class="date-divider-date">
                {{ getLocalMoment(message.created_at).format('DD/MMM/YYYY') }}
            </div>
        </Divider>
        <div class="flex justify-start items-start w-full" :class="isMyMessage ? 'flex-row-reverse' : null">
            <div v-if="mustShowSender"
                class="sender-avatar mx-1 mt-1"
                :style="{backgroundColor: sender.personal_color}"
                @click="onShowProfile(sender)"
                v-tooltip.left="sender.name"
            >
                <div class="sender-avatar rounded-full w-full h-full bg-cover bg-center"
                    :style="
                    {
                        border: `#5dbea3 solid 1px`,
                        backgroundColor: sender.personal_color,
                        backgroundImage: `url(${sender.avatar_path})`
                    }"
                >
                </div>
            </div>
            <div :class="isMyMessage ? 'my-message-container': 'others-message-container'">
                <span>
                    {{ message.text }}
                </span>
                <span class="float-right message-info relative"
                    :class="{seen: isSeen && isMyMessage, right: isMyMessage, left: !isMyMessage}"
                    v-tooltip.top="
                    isSeen ?
                    { value: computedSeenByMessage, class: 'users-seen-tooltip' }
                    : null"
                >
                    <span class="ml-3 message-time">{{ getLocalMoment(message.created_at).format('HH:mm') }}</span>
                    <span v-if="isMyMessage">
                        <i class="pi pi-check seen-icon"></i>
                        <i :class="{'hide': !isSeen}" class="pi pi-check seen-icon relative" style="right: 10px"></i>
                    </span>
                </span>
            </div>
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
.message-info.left {
    top: 6px;
    left: 6px;
}
.message-info.right {
    top: 6px;
    left: 15px;
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
.seen-icon.hide {
    opacity: 0;
}
.users-seen-tooltip {
    font-size: 11px;
}
.message-time,
.seen-icon {
    font-size: 10px;
}
.p-divider-content {
    background-color: rgba(220,220,200,1);
    color: rgba(10,10,10,.8);
    border-radius: 20px;
    font-size: 12px;
    padding: 0px 12px;
}

</style>
