<script setup>
    import { Card, Button, InputText, Toast, Message, Password, FloatLabel } from 'primevue';
    import { ref } from 'vue';
    import { Form } from '@primevue/forms';
    import { zodResolver } from '@primevue/forms/resolvers/zod';
    import { useToast } from "primevue/usetoast";
    import { z } from 'zod';
    import { primeVueFormStatesToData } from '@/utils/helpers';
    import axios from 'axios';
    import router from '@/router';
    import { useAppStore } from '@/stores/useApp'
    import { onMounted } from 'vue';

    const { setContentIsReady } = useAppStore();

    onMounted(() => setContentIsReady(true))

    const toast = useToast();
    const initialValues = ref({
        name: 'Ouss Dgun',
        email: 'ousdgun@gmail.com',
        password: 'password',
        password_confirmation: 'password',
        remember: false
    });

    const formResolver = ref(zodResolver(
        z.object({
            name: z.string().min(1, { message: 'Name is required.' }),
            email: z.string().min(1, { message: 'Email is required.' }).email({ message: 'Invalid email address.' }),
            password: z.string().min(1, { message: 'Password is required.' }).min(8, {message: 'Password need to be atleast 8 charachters.'}),
            remember: z.boolean()
        })
    ));

    const serverErrors = ref({});

    const onFormSubmit = ({ valid, states }) => {
        if (valid) {
            axios.post('/register', primeVueFormStatesToData(states))
            .then(response => {
                const responseData = response['data'];
                if ( responseData['success'] && responseData['redirect'] ) {
                    toast.add({ severity: 'success', summary: 'Registering...', life: 3000 });
                    router.push({ path: responseData['redirect'] })
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
        <div class="auth-container flex justify-center">
            <Card style="width: 25rem; overflow: hidden" class="shadow-xl">
                <template #header>
                    <div class="flex justify-center items-center">
                        <img alt="login header image" width="120" src="/images/logo.png" />
                    </div>
                </template>
                <template #title>
                    <h1 class="text-3xl font-bold text-center">Welcome to VuejsChat</h1>
                </template>
                <template #subtitle>
                    <p class="text-center">Create a new account</p>
                </template>
                <template #content>
                    <div class="login-form">
                        <Toast />
                        <Form v-slot="$form" :resolver="formResolver" :initialValues="initialValues" @submit="onFormSubmit" class="flex justify-center flex-col gap-4" >
                            <div class="flex flex-col mb-2">
                                <FloatLabel>
                                    <InputText name="name" type="text" inputId="name_label" class="w-full"  />
                                    <label for="name_label">Name</label>
                                </FloatLabel>
                                <Message v-if="$form.name?.invalid" severity="error" size="small" variant="simple">{{ $form.name.error?.message }}</Message>
                                <Message v-if="serverErrors.name" severity="error" size="small" variant="simple">{{ serverErrors.name }}</Message>
                            </div>
                            <div class="flex flex-col mb-2">
                                <FloatLabel>
                                    <InputText name="email" type="email" inputId="email_label" class="w-full"  />
                                    <label for="email_label">Email</label>
                                </FloatLabel>
                                <Message v-if="$form.email?.invalid" severity="error" size="small" variant="simple">{{ $form.email.error?.message }}</Message>
                                <Message v-if="serverErrors.email" severity="error" size="small" variant="simple">{{ serverErrors.email }}</Message>
                            </div>
                            <div class="flex flex-col">
                                <FloatLabel>
                                    <Password name="password"  fluid inputId="password_label" class="w-full" toggleMask />
                                    <label for="password_label">Password</label>
                                </FloatLabel>
                                <template v-if="$form.password?.invalid">
                                    <Message v-for="(error, index) of $form.password.errors" :key="index" severity="error" size="small" variant="simple">{{ error.message }}</Message>
                                </template>
                                <Message v-if="serverErrors.password" severity="error" size="small" variant="simple">{{ serverErrors.password }}</Message>
                            </div>
                            <div class="flex flex-col">
                                <FloatLabel>
                                    <Password name="password_confirmation"  fluid inputId="password_confirmation_label" class="w-full" toggleMask />
                                    <label for="password_confirmation_label">Password Confirmation</label>
                                </FloatLabel>
                                <template v-if="$form.password_confirmation?.invalid">
                                    <Message v-for="(error, index) of $form.password_confirmation.errors" :key="index" severity="error" size="small" variant="simple">{{ error.message }}</Message>
                                </template>
                                <Message v-if="serverErrors.password_confirmation" severity="error" size="small" variant="simple">{{ serverErrors.password_confirmation }}</Message>
                            </div>
                            <div class="flex justify-end mt-5">
                                <Button type="submit" severity="secondary" raised class="!py-1 !px-3 sm:!py-2 sm:!px-5 !rounded-md">Register</Button>
                            </div>
                        </Form>
                    </div>
                </template>
                <template #footer>
                </template>
            </Card>
        </div>
    </div>
</template>
