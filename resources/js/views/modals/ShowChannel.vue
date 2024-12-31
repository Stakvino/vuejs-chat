<script setup>
import Dialog from 'primevue/dialog';
import { ref } from "vue";
import { Avatar, Divider } from 'primevue';
import axios from 'axios';
import ShowProfile from '@/views/modals/ShowProfile.vue';

defineProps(['channel']);

const isVisible = defineModel();
const message = ref();
const selectedUser = ref();
const showProfileIsVisible = ref(false);
const onShowProfile = user => {
    axios.get(`/api/users/profile/${user.id}`)
    .then(response => {
        selectedUser.value = response.data['user'];
        showProfileIsVisible.value = true;
    })
}
</script>

<template>
    <div v-if="channel">
        <Dialog dismissableMask v-model:visible="isVisible" position="top" modal :header="channel.name" :style="{ width: '25rem' }">
            <div class="flex justify-center items-start p-2 rounded">
                <div class="relative">
                    <Avatar
                        size="xlarge"
                        class="w-28 h-28 user-avatar bg-cover bg-center bg-no-repeat"
                        aria-controls="profile_menu"
                        alt="channel icon"
                        shape="circle"
                        :style="{backgroundImage: `url(${channel.icon})`, border: 'rgb(150,150,150) solid 1px'}"
                    />
                </div>
            </div>
            <div class="flex flex-col gap-1 mb-4">
                <label for="name" class="font-semibold w-24">Channel</label>
                <p>{{ channel.name }}</p>
            </div>
            <div class="flex flex-col gap-1 mb-4">
                <label for="username" class="font-semibold w-24">Users</label>
                <div class="h-52 overflow-y-auto border p-2 rounded">
                    <ShowProfile :user="selectedUser" v-model="showProfileIsVisible" />
                    <div v-for="user of channel.users" @click="onShowProfile(user)" class="cursor-pointer">
                        <div class="flex flex-1 items-center gap-2 w-8/12">
                            <div class="rounded-full w-8 h-8 bg-cover"
                                :style="{
                                backgroundColor: user.personal_color,
                                backgroundImage: `url(${user.avatar_path})`            }"
                            >
                            </div>
                            <div class="flex flex-col max-w-full">
                                <span class="font-bold">{{ user.name }}</span>
                                <p class="text-sm truncate w-10/12">Last login : {{ user.last_login_at }}</p>
                            </div>
                        </div>
                        <Divider class="my-2" />
                    </div>
                </div>
            </div>
        </Dialog>
    </div>
</template>
