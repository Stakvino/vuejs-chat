<script setup>
import Dialog from 'primevue/dialog';
import { storeToRefs } from 'pinia';
import { useModalStore } from '@/stores/useModal';
import { useAuthStore } from '@/stores/useAuth';
import { InputText, Button, Avatar, Message, Toast } from 'primevue';
import { useToast } from "primevue/usetoast";
import FileUpload from '@/components/FileUpload/FileUpload.vue';
import { onMounted, ref, useTemplateRef, watch } from 'vue';
import axios from 'axios';

const modalStore = useModalStore();
const { editProfileModalIsVisible } = storeToRefs(modalStore);
const { fetchAuthUser } = useAuthStore();
let avatarFileUpload = null;
const getInputRef = (inputRef) => { avatarFileUpload =  inputRef }

const authStore = useAuthStore();
const { authUser } = storeToRefs(authStore)

const updateData = ref({
    name: authUser.name,
    avatar: null,
});

onMounted(() => {
    updateData.value.name = authUser.value.name;
    updateData.value.avatar = authUser.value.avatar;
})

const avatarPath = ref(authUser.value['avatar_path']);
const onAvatarChange = () => {
    const files = avatarFileUpload.value.files;
    if (FileReader && files && files.length) {
        const img = new FileReader();
        img.onload = function () {
            avatarPath.value = img.result;
            showAvatarDeleteBtn.value = true;
        }
        img.readAsDataURL(files[0]);
    }
}

const defaultAvatarPath = "/images/avatars/default.png";
const showAvatarDeleteBtn = ref(avatarPath.value !== defaultAvatarPath);
const onDeleteAvatar = () => {
    updateData.value.avatar = null;
    avatarPath.value = defaultAvatarPath;
    showAvatarDeleteBtn.value = false;
    avatarFileUpload.value.value = null;
}

const serverErrors = ref({});
const toast = useToast();
const onSave = () => {
    if (avatarFileUpload.value.files.length) {
        updateData.value.avatar = avatarFileUpload.value.files[0];
    }
    var formData = new FormData();
    formData.append('name', updateData.value.name);
    formData.append('avatar', updateData.value.avatar);
    formData.append('_method', 'PUT');

    axios.post('/api/user/profile/update', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(response => {
        const responseData = response['data'];
        if ( responseData['success'] ) {
            fetchAuthUser(() => {
                editProfileModalIsVisible.value = false;
                toast.add({ severity: 'success', summary: 'Profile edit saved.', life: 3000 });
            });
        }
        else if ( responseData['validation_error'] && responseData['error_messages'] ) {
            for (const fieldName in responseData['error_messages']) {
                if (Object.prototype.hasOwnProperty.call(responseData['error_messages'], fieldName)) {
                    const errorMessages = responseData['error_messages'][fieldName];
                    serverErrors.value[fieldName] = errorMessages.join(' | ');
                }
            }
        }
        else {
            toast.add({ severity: 'error', summary: 'Response error from server.', life: 3000 });
        }
    })
    .catch(e => console.log('catch error response', e))
}

</script>

<template>
    <div>
        <Toast />
        <Dialog v-model:visible="editProfileModalIsVisible" position="top" modal header="Edit Profile" :style="{ width: '25rem' }">
            <div class="flex justify-center items-start my-4 border p-2 rounded">
                <div class="relative">
                    <Button v-if="showAvatarDeleteBtn" type="button" class="error-button w-6 h-6 !rounded-full top-0 right-0 absolute border border-slate-50" icon="pi pi-times text-xs" raised aria-label="delete avatar" size="small" @click="onDeleteAvatar"></Button>
                    <FileUpload @change="onAvatarChange" :getInputRef="getInputRef" v-show="false" v-model="updateData.avatar" name="profile_avatar" accept="image/*" :maxFileSize="1000000" />
                    <Avatar
                        @click="avatarFileUpload.click()"
                        size="xlarge"
                        class="w-28 h-28 cursor-pointer user-avatar bg-cover bg-center bg-no-repeat"
                        aria-controls="profile_menu"
                        alt="user profile pic"
                        shape="circle"
                        :style="{backgroundColor: authUser['personal_color'], backgroundImage: `url(${avatarPath})`, border: 'rgb(150,150,150) solid 1px'}"
                    />
                </div>
            </div>
            <div class="flex flex-col gap-1 mb-4">
                <label for="name" class="font-semibold w-24">Name</label>
                <InputText v-model="updateData.name" id="name" class="flex-auto" autocomplete="off" />
                <Message v-if="serverErrors.name" severity="error" size="small" variant="simple">{{ serverErrors.name }}</Message>
            </div>
            <div class="flex flex-col gap-1 mb-4">
                <label for="username" class="font-semibold w-24">Username</label>
                <InputText :value="authUser.username" id="username" class="flex-auto" autocomplete="off" disabled />
            </div>
            <div class="flex flex-col gap-1 mb-4">
                <label for="email" class="font-semibold w-24">Email</label>
                <InputText :value="authUser.email" id="email" class="flex-auto" autocomplete="off" disabled />
            </div>
            <div class="flex justify-end gap-2 mt-8">
                <Button class="!py-1 !px-3 sm:!py-2 sm:!px-5 !rounded-md mr-2" raised type="button" label="Cancel" severity="secondary" @click="editProfileModalIsVisible = false"></Button>
                <Button class="action-button !py-1 !px-3 sm:!py-2 sm:!px-5 !rounded-md" raised type="button" label="Save" @click="onSave"></Button>
            </div>
        </Dialog>
    </div>
</template>
