<script setup>
import axios from 'axios';
import { computed, onUpdated, ref } from 'vue';
import { AutoComplete, Button, IconField, InputIcon, InputText, Menu } from 'primevue';
import ShowProfile from '@/views/modals/ShowProfile.vue';
import { dateTimeFormat } from '@/utils/helpers';
import { useModalStore } from '@/stores/useModal';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/useApp';

const props = defineProps(['selectedChannel', 'messageSentEventUpdate', 'goToChannel']);

const showChannelsList = defineModel('showChannelsList');

const modalStore = useModalStore();
const { isProfileModalVisible } = storeToRefs(modalStore);

const appStore = useAppStore();
const { isMobileScreen } = storeToRefs(appStore);

const searchInputShow = ref(false);
const selectedCountry = ref();
const filteredCountries = ref();
const userOptionsMenu = ref();

const userOptionsMenuItems = ref([
    {
        items: [
            { label: "Options" },
            { label: 'Block user', icon: 'pi pi-ban' },
        ]
    }
]);

const toggleUserOptionsMenu = (event) => {
    userOptionsMenu.value.toggle(event);
};

const userChatSearch = (event) => {
    setTimeout(() => {
        filteredCountries.value = [{name: "Algeria"}, {name: "Canada"}]
    }, 250);
}

// The user that can send messages to auth in this channel
const receiver = computed(() => props.selectedChannel.receivers[0]);
const senderProfile = ref();
const onShowProfile = () => {
    axios.get(`/api/users/profile/${receiver.value.id}`)
    .then(response => {
        senderProfile.value = response.data['user'];
        isProfileModalVisible.value = true;
    })
}

const lastMessage = computed(() => {
    return props.selectedChannel.messages.slice(-1).pop();
});

</script>

<template>
    <div class="bg-white p-2">
        <div class="flex justify-start items-center flex-1 gap-2">
            <div>
                <Button v-if="isMobileScreen" @click="showChannelsList = true" icon="pi pi-arrow-left text-black text-sm" rounded aria-label="Back" raised />
            </div>
            <ShowProfile :user="senderProfile" v-model="isProfileModalVisible"
                :messageSentEventUpdate="messageSentEventUpdate"
                :goToChannel="goToChannel"
            />
            <span class="flex justify-start items-center gap-2" @click="onShowProfile">
                <div class="cursor-pointer min-w-10">
                    <div class="rounded-full w-12 h-12 bg-cover bg-center"
                        :style="{
                            border: `#5dbea3 solid 1px`,
                            backgroundColor: receiver.personal_color,
                            backgroundImage: `url(${receiver.avatar_path})`
                        }"
                    >
                    </div>
                </div>
                <div class="flex flex-col" v-if="!searchInputShow">
                    <span class="font-bold">{{ receiver.name }}</span>
                    <span class="text-xs">Last seen {{ dateTimeFormat(receiver.last_login_at) }}</span>
                </div>
            </span>
            <div v-if="searchInputShow" class="chat-search w-full flex justify-center">
                <AutoComplete class="w-full" v-model="selectedCountry" optionLabel="Search in chat" :suggestions="filteredCountries" @complete="userChatSearch" placeholder="Search in chat">
                    <template #option="slotProps">
                        <div class="flex items-center">
                            <img :alt="slotProps.option.name" src="https://primefaces.org/cdn/primevue/images/flag/flag_placeholder.png" :class="`flag flag-trash mr-2`" style="width: 18px" />
                            <div>{{ slotProps.option.name }}</div>
                        </div>
                    </template>
                    <template #header></template>
                    <template #footer></template>
                </AutoComplete>
            </div>
            <div class="ml-auto" v-if="searchInputShow" @click="searchInputShow = false">
                <Button style="color: black;" icon="pi pi-times" aria-label="Close search" />
            </div>
            <!--
            <div class="ml-auto" v-if="!searchInputShow" @click="searchInputShow = true">
                <Button style="color: black;" icon="pi pi-search" aria-label="Search in chat" />
            </div>
            -->
            <div class="ml-auto">
                <Button class="three-dots-btn" type="button" icon="pi pi-ellipsis-v" @click="toggleUserOptionsMenu" size="large" aria-haspopup="true" aria-controls="user_options_menu" />
                <Menu ref="userOptionsMenu" id="user_options_menu" :model="userOptionsMenuItems" :popup="true" />
            </div>
        </div>
    </div>
</template>
