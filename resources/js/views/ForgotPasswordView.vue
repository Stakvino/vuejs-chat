<script setup>
import { Card, Button, InputText, Message, useToast, Toast } from 'primevue';
import Divider from 'primevue/divider';
import { ref, onMounted } from 'vue';
import { useAppStore } from '@/stores/useApp'
import axios from 'axios';

const { setContentIsReady } = useAppStore();
onMounted(() => setContentIsReady(true));

const userEmail = ref();
const serverErrors = ref({});
const toast = useToast();
const showSuccessMessage = ref(false);
const showSuccessMessageDelay = 6000;

const onSendLinkClick = () => {
    axios.post('/forgot-password', { email: userEmail.value })
    .then(response => {
        const responseData = response['data'];
        if ( responseData['success'] && responseData['success_message'] ) {
            toast.add({ severity: 'success', summary: responseData['success_message'], life: 3000 });
            showSuccessMessage.value = true;
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, showSuccessMessageDelay);
        }
        else if ( responseData['validation_error'] && responseData['error_messages'] ) {
            for (const fieldName in responseData['error_messages']) {
                if (Object.prototype.hasOwnProperty.call(responseData['error_messages'], fieldName)) {
                    const errorMessages = responseData['error_messages'][fieldName];
                    serverErrors.value[fieldName] = errorMessages.join(' | ');
                }
            }
        }
    })
}
</script>

<template>
    <div class="mt-10">
        <Toast />
        <div class="auth-container flex flex-col items-center">
            <div>
                <img width="60" src="/images/logo.png" alt="website logo">
            </div>
            <Card>
                <template #title>Forgot password ?</template>
                <template #content>
                    <p class="m-0">
                        Enter the email address linked to your account and we'll send you an email.
                    </p>

                    <Divider class="border my-6" />

                    <div class="flex flex-col gap-2 mt-4">
                        <label class="font-bold" for="userEmail">Email</label>
                        <InputText id="userEmail" v-model="userEmail" aria-describedby="email-help" />
                        <Message v-if="serverErrors.email" severity="error" size="small" variant="simple">{{ serverErrors.email }}</Message>
                    </div>
                    <div class="flex justify-center mt-4">
                        <Button type="button" @click="onSendLinkClick" rounded class="action-button p-2 w-full" raised>Send link</Button>
                    </div>
                    <div class="mt-4">
                        <Message class="p-2" v-if="showSuccessMessage" severity="success" :life="showSuccessMessageDelay">
                            We have emailed your password reset link.
                        </Message>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
