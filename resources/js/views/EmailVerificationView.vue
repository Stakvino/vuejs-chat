<script setup>
import { Card, Button, useToast, Toast, Message } from 'primevue';
import { ref, onMounted } from 'vue';
import { useAppStore } from '@/stores/useApp'
import axios from 'axios';

const toast = useToast();
const showSuccessMessage = ref(false);
const showSuccessMessageDelay = 6000;

const onEmailVerification = () => {
    axios.post('/email/verification-notification', {})
    .then(response => {
        const responseData = response['data'];
        if ( responseData['success'] ) {
            toast.add({ severity: 'success', summary: `An email has been sent to your address.`, life: 3000 });
            showSuccessMessage.value = true;
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, showSuccessMessageDelay);
        }
    });
}

const { setContentIsReady } = useAppStore();
onMounted(() => {
    setContentIsReady(true);
    setTimeout(() => {
        onEmailVerification();
    }, 1000);
});

</script>

<template>
    <div class="mt-10">
        <Toast />
        <div class="auth-container flex flex-col items-center">
            <div>
                <img width="100" src="/images/logo.png" alt="website logo">
            </div>
            <Card>
                <template #title>
                    <p class="font-bold">Email verification</p>
                </template>
                <template #content>
                    <p class="m-0">
                        Before proceeding please check your email for a verification link.
                        if you did not receive the email <Button @click="onEmailVerification" class="!text-blue-500 inline">Click here to request another</Button>
                    </p>
                </template>
            </Card>
            <div class="mt-4">
                <Message class="p-2" v-if="showSuccessMessage" severity="success" :life="showSuccessMessageDelay">
                    An email has been sent to your address.
                </Message>
            </div>
        </div>
    </div>
</template>
