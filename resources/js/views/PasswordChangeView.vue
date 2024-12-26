<script setup>
import { Card, Button, FloatLabel, Message, Password, Toast } from 'primevue';
import { primeVueFormStatesToData } from '@/utils/helpers';
import { Form } from '@primevue/forms';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import { useToast } from "primevue/usetoast";
import { z } from 'zod';
import Divider from 'primevue/divider';
import { ref, onMounted } from 'vue';
import { useAppStore } from '@/stores/useApp'
import router from '@/router';
import axios from 'axios';

const { setContentIsReady } = useAppStore();
onMounted(() => setContentIsReady(true));

const toast = useToast();
const initialValues = ref({
    password: 'newpassword',
    password_confirmation: 'newpassword',
});

const formResolver = ref(zodResolver(
    z.object({
        password: z.string().min(1, { message: 'Password is required.' }).min(8, {message: 'Password need to be atleast 8 charachters.'}),
        password_confirmation: z.string()
        .min(1, { message: 'Password Confirmation is required.' })
        .min(8, {message: 'Password Confirmation need to be atleast 8 charachters.'}),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: "Passwords don't match",
        path: ["password_confirmation"],
    })
));

const serverErrors = ref({});

const onFormSubmit = ({ valid, states }) => {
    if (valid) {
        axios.post('/update-password', primeVueFormStatesToData(states))
        .then(response => {
            const responseData = response['data'];
            if ( responseData['success'] && responseData['success_message'] ) {
                toast.add({ severity: 'success', summary: responseData['success_message'], life: 3000 });
                router.push({ path: '/login', query: {'success-message': 'Your new password has been saved'} });
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
};
</script>

<template>
    <div class="mt-10">
        <Toast />
        <div class="auth-container flex flex-col items-center">
            <div>
                <img width="60" src="/images/logo.png" alt="website logo">
            </div>
            <Card class="w-11/12 xl:w-4/12 md:w-6/12">
                <template #title>
                    <p class="font-bold">Change your password</p>
                    <Divider class="border mt-4 mb-0" />
                </template>
                <template #content>
                    <Form v-slot="$form" :resolver="formResolver" :initialValues="initialValues" @submit="onFormSubmit" class="mt-2 flex justify-center flex-col gap-4 w-full" >
                        <div class="flex flex-col">
                            <div class="flex flex-col gap-2">
                                <label for="current_password_label">Current password</label>
                                <Password name="current_password" fluid inputId="current_password_label" class="w-full" toggleMask :feedback="false" />
                            </div>
                            <template v-if="$form.current_password?.invalid">
                                <Message v-for="(error, index) of $form.current_password.errors" :key="index" severity="error" size="small" variant="simple">{{ error.message }}</Message>
                            </template>
                            <Message v-if="serverErrors.current_password" severity="error" size="small" variant="simple">{{ serverErrors.current_password }}</Message>
                        </div>
                        <div class="flex flex-col">
                            <div class="flex flex-col gap-2">
                                <label for="password_label">New password</label>
                                <Password name="password"  fluid inputId="password_label" class="w-full" toggleMask />
                            </div>
                            <template v-if="$form.password?.invalid">
                                <Message v-for="(error, index) of $form.password.errors" :key="index" severity="error" size="small" variant="simple">{{ error.message }}</Message>
                            </template>
                            <Message v-if="serverErrors.password" severity="error" size="small" variant="simple">{{ serverErrors.password }}</Message>
                        </div>
                        <div class="flex flex-col">
                            <div class="flex flex-col gap-2">
                                <label for="password_confirmation_label">New password confirmation</label>
                                <Password name="password_confirmation"  fluid inputId="password_confirmation_label" class="w-full" toggleMask />
                            </div>
                            <template v-if="$form.password_confirmation?.invalid">
                                <Message v-for="(error, index) of $form.password_confirmation.errors" :key="index" severity="error" size="small" variant="simple">{{ error.message }}</Message>
                            </template>
                            <Message v-if="serverErrors.password_confirmation" severity="error" size="small" variant="simple">{{ serverErrors.password_confirmation }}</Message>
                            <Message v-if="serverErrors.email" severity="error" size="small" variant="simple">{{ serverErrors.email }}</Message>
                        </div>

                        <div class="flex justify-center mt-4">
                            <Button type="submit" rounded class="p-2 action-button w-full" raised>Send</Button>
                        </div>
                    </Form>
                </template>
            </Card>
        </div>
    </div>
</template>
