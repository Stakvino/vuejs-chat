<script setup>
import { computed, onUpdated, ref, useTemplateRef, watchEffect } from 'vue';
import ShowProfile from '@/views/modals/ShowProfile.vue';
import { dateTimeFormat } from '@/utils/helpers';
import { getLocalMoment } from '@/utils/helpers';
import { useAuthStore } from '@/stores/useAuth';
import { storeToRefs } from 'pinia';
import { Button, Divider, Image, Menu, useConfirm } from 'primevue';
import AudioMessageReader from '../AudioMessageReader/AudioMessageReader.vue';
import axios from 'axios';

const props = defineProps([
    "message", "usersSeen", "isMyMessage", "sender", "mustShowSender", "onShowProfile",
    "dateDivider"
])

const emits = defineEmits(['reload-messages']);

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

const messageOptionsMenu = ref();

const confirm = useConfirm();

const deleteMessageConfirm = () => {
    confirm.require({
        message: 'Are you sure you want to delete this message ?',
        header: 'Confirmation',
        icon: 'pi pi-info-circle',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true,
            class: 'action-button !rounded py-1 px-3 w-20'
        },
        acceptProps: {
            label: 'Delete',
            class: 'error-button rounded py-1 px-3 w-20'
        },
        accept: () => {
            axios.delete(`/api/messages/${props.message.id}`)
            .then(response => {
                if (response.data.success) {
                    emits('reload-messages');
                }
            })
        }
    });
}

const messageOptionsItems = ref
([
    { label: "Options" },
    {
        label: 'Delete message',
        icon: 'pi pi-trash',
        command: () => {
            deleteMessageConfirm();
        }
    },
]);

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

            <div v-if="message.is_deleted" class="relative" :class="isMyMessage ? 'my-message-container': 'others-message-container'">
                <span class="italic font-bold">Deleted message</span>
            </div>

            <div v-else class="relative" :class="isMyMessage ? 'my-message-container': 'others-message-container'">
                <div v-if="isMyMessage" class="absolute" style="right: 5px; top: 2px">
                    <Button class="three-dots-btn" type="button" icon="pi pi-ellipsis-v text-white font-extrabold"
                        @click="event => messageOptionsMenu.toggle(event)" size="small"
                        aria-haspopup="true" aria-controls="message_options_menu"
                    />
                    <Menu ref="messageOptionsMenu" id="message_options_menu" :model="messageOptionsItems" :popup="true" />
                </div>
                <span v-if="message.info.is_image">
                    <div class="flex flex-col items-end justify-end">
                        <Image :src="message.info.file_path" alt="Image" width="80" height="60" preview />
                        <div class="flex items-end mt-2">
                            <div>
                                <Button
                                    class="action-button border border-white" as="a"
                                    style="width: 25px; height: 25px" size="small" raised rounded
                                    icon="pi pi-download text-xs" title="Download file"
                                    :href="message.info.file_path" :download="message.text"
                                />
                            </div>
                            <span class="message-info ml-auto"
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
                </span>
                <span v-else-if="message.info.is_file">
                    <div class="flex flex-col items-end justify-end">
                        <div class="bg-gray-500 rounded">
                            <i class="pi pi-file text-5xl p-2"></i>
                        </div>
                        <div class="flex items-end mt-2">
                            <div>
                                <Button class="action-button border border-white" as="a"
                                    style="width: 25px; height: 25px" size="small" raised rounded
                                    icon="pi pi-download text-xs" title="Download file"
                                    :href="message.info.file_path" :download="message.text"
                                />
                            </div>
                            <span class="message-info ml-auto"
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
                </span>

                <span v-else-if="message.info.is_audio">
                    <div class="flex flex-col items-end justify-end">
                        <AudioMessageReader :filePath="message.info.audio_path" />
                        <div class="flex items-end mt-2">
                            <span class="message-info ml-auto"
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
                </span>

                <span v-else>
                    <span>{{ message.text }}</span>
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
    padding: 10px 25px 8px 15px;
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
