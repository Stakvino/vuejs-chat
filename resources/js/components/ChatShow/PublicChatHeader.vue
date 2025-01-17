<script setup>
import axios from 'axios';
import { computed, ref } from 'vue';
import { AutoComplete, Button, ConfirmDialog, IconField, InputIcon, InputText, Menu, Toast, useConfirm, useToast } from 'primevue';
import ShowChannel from '@/views/modals/ShowChannel.vue';
import { dateTimeFormat } from '@/utils/helpers';
import { useModalStore } from '@/stores/useModal';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/useApp';

const props = defineProps(['selectedChannel', 'messageSentEventUpdate', 'goToChannel', 'deleteChannel']);

const showChannelsList = defineModel('showChannelsList');

const appStore = useAppStore();
const { isMobileScreen } = storeToRefs(appStore);

const modalStore = useModalStore();
const { isChannelModalVisible } = storeToRefs(modalStore);
const searchInputShow = ref(false);
const selectedCountry = ref();
const filteredCountries = ref();
const userOptionsMenu = ref();

const confirm = useConfirm();

const leaveChannelConfirm = () => {
    confirm.require({
        message: 'Are you sure you want to leave this channel ?',
        header: 'Confirmation',
        icon: 'pi pi-info-circle',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true,
            class: 'action-button !rounded py-1 px-3 w-20'
        },
        acceptProps: {
            label: 'Leave',
            class: 'error-button rounded py-1 px-3 w-20'
        },
        accept: () => {
            axios.delete(`/api/channels/unsubscribe/${props.selectedChannel.id}`)
            .then(response => {
                if ( response.data.success ) {
                    props.deleteChannel(props.selectedChannel.id);
                }
            })
        },
        reject: () => {

        }
    });
}

const userOptionsMenuItems = ref([
    {
        items: [
            { label: "Options" },
            {
                label: 'Leave channel',
                icon: 'pi pi-ban',
                command: () => {
                    leaveChannelConfirm();
                }
            },
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


// Clicking on channel icon will show a modal with channel infos
const showChannelIsVisible = ref(false);
const channel = ref();
const onShowChannel = () => {
    axios.get(`/api/channels/${props.selectedChannel.id}`)
    .then(response => {
        channel.value = response.data['channel'];
        isChannelModalVisible.value = true;
    });
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
            <ShowChannel :channel="channel" v-model="isChannelModalVisible"
                :messageSentEventUpdate="messageSentEventUpdate"
                :goToChannel="goToChannel"
            />
            <span @click="onShowChannel" class="flex justify-start items-center gap-2">
                <div class="cursor-pointer min-w-10">
                    <div class="rounded-full w-12 h-12 bg-cover"
                        :style="{ backgroundImage: `url(${selectedChannel.icon})` }"
                    >
                    </div>
                </div>
                <div class="flex flex-col" v-if="!searchInputShow">
                    <span class="font-bold">{{ selectedChannel.name }}</span>
                    <span v-if="lastMessage" class="text-xs">Last message {{ dateTimeFormat(lastMessage.created_at) }}</span>
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
        <ConfirmDialog></ConfirmDialog>
    </div>
</template>
