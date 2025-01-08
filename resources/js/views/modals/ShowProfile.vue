<script setup>
import Dialog from 'primevue/dialog';
import { ref, onMounted, watchEffect } from "vue";
import { InputText, Button, Avatar, Message, Toast, Textarea } from 'primevue';
import { useToast } from "primevue/usetoast";
import { useAuthStore } from '@/stores/useAuth';
import { useModalStore } from '@/stores/useModal';
import { storeToRefs } from 'pinia';
import axios from 'axios';

const props = defineProps(['user', 'messageSentEventUpdate']);

const modalStore = useModalStore();
const { isProfileModalVisible, isChannelModalVisible } = storeToRefs(modalStore);
const authStore = useAuthStore();
const { authUser } = storeToRefs(authStore);

const isVisible = defineModel();
const message = ref();
const onMessageSubmit = () => {
    axios.post('/api/messages', { 'text': message.value, 'receiver_id': props.user.id })
    .then(response => {
        if ( response.data['success'] ) {
            const updatedChannel = response.data.channel;
            const newMessage = response.data.message;
            props.messageSentEventUpdate(updatedChannel, newMessage);
            message.value = '';
            isChannelModalVisible.value = false;
            isProfileModalVisible.value = false;
            goToChannel(updatedChannel.id);
        }
    });
}
</script>

<template>
    <div v-if="user">
        <Dialog dismissableMask v-model:visible="isVisible" position="top" modal :header="user.name" :style="{ width: '25rem' }">
            <div class="flex justify-center items-start p-2 rounded">
                <div class="relative">
                    <Avatar
                        size="xlarge"
                        class="w-28 h-28 user-avatar bg-cover bg-center bg-no-repeat"
                        aria-controls="profile_menu"
                        alt="user profile pic"
                        shape="circle"
                        :style="{backgroundColor: user.personal_color, backgroundImage: `url(${user.avatar_path})`, border: 'rgb(150,150,150) solid 1px'}"
                    />
                </div>
            </div>
            <div class="flex flex-col gap-1 mb-4">
                <label for="name" class="font-semibold w-24">Name</label>
                <p>{{ user.name }}</p>
            </div>
            <div class="flex flex-col gap-1 mb-4">
                <label for="username" class="font-semibold w-24">Username</label>
                <p>{{ user.username }}</p>
            </div>
            <div v-if="authUser.id !== user.id" class="flex gap-2 mt-8">
                <form @submit.prevent="onMessageSubmit" class="w-full">
                    <div class="flex flex-col gap-1 mb-4">
                        <label for="message_input" class="font-semibold w-24">Message</label>
                        <Textarea v-model="message" id="message_input" rows="2" cols="30" />
                    </div>
                    <Button class="action-button !py-1 !px-3 sm:!py-2 sm:!px-5 !rounded-md" raised type="submit" label="Send message"></Button>
                </form>
            </div>
            <div v-else class="mt-8" style="height: 150px;"></div>
        </Dialog>
    </div>
</template>
