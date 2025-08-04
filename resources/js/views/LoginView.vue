<script setup>
    import { Card, Button, InputText, Toast, Message, Checkbox, Password, FloatLabel } from 'primevue';
    import { ref, onMounted } from 'vue';
    import { Form } from '@primevue/forms';
    import { zodResolver } from '@primevue/forms/resolvers/zod';
    import { useToast } from "primevue/usetoast";
    import { z } from 'zod';
    import { useAppStore } from '@/stores/useApp';
    import { primeVueFormStatesToData } from '@/utils/helpers';
    import axios from 'axios';
    import router from '@/router';

    axios.defaults.headers.common = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    };
    axios.defaults.withCredentials = true;
    axios.defaults.withXSRFToken = true;

    const { setContentIsReady } = useAppStore();

    const urlQuery = router.currentRoute.value.query;
    const successMessage = urlQuery['success-message'] || false;

    onMounted(() => setContentIsReady(true))

    const toast = useToast();
    const initialValues = ref({
        email: '',
        password: '',
        remember: false,
        "_token": document.querySelector("meta[name=csrf-token]").content
    });

    const resolver = ref(zodResolver(
        z.object({
            email: z.string().min(1, { message: 'Email is required.' }).email({ message: 'Invalid email address.' }),
            password: z.string().min(1, { message: 'Password is required.' }).min(8, {message: 'Password need to be atleast 8 charachters.'}),
            remember: z.boolean()
        })
    ));

    const serverErrors = ref({});
    const onFormSubmit = ({ valid, states }) => {
        if (valid) {
            axios.post(
                '/login',
                primeVueFormStatesToData(states),
                {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector("meta[name=csrf-token]").getAttribute('content')
                    }
                }
            )
            .then(response => {
                const responseData = response['data'];
                if ( responseData['success'] && responseData['redirect'] ) {
                    toast.add({ severity: 'success', summary: 'Login in...', life: 3000 });
                    router.push({ path: responseData['redirect'] });
                }
                else if ( responseData['validation_error'] && responseData['error_messages'] ) {
                    for (const fieldName in responseData['error_messages']) {
                        if (Object.prototype.hasOwnProperty.call(responseData['error_messages'], fieldName)) {
                            const errorMessage = responseData['error_messages'][fieldName];
                            serverErrors.value[fieldName] = errorMessage;
                        }
                    }
                }
                else {
                    toast.add({ severity: 'error', summary: 'Response error from server.', life: 3000 });
                    console.log(responseData["error_message"]);
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
                    <Message class="p-2" v-if="successMessage" severity="success">
                        {{ successMessage }}
                    </Message>
                    <div class="flex justify-center items-center">
                        <img alt="login header image" width="120" src="/images/logo.png" />
                    </div>
                </template>
                <template #title>
                    <h1 class="text-3xl font-bold text-center">Welcome to VuejsChat</h1>
                </template>
                <template #subtitle>
                    <p class="text-center">Sign in to continue</p>
                </template>
                <template #content>
                    <div class="login-form">
                        <Toast />
                        <Form v-slot="$form" :resolver="resolver" :initialValues="initialValues" @submit="onFormSubmit" class="flex justify-center flex-col gap-4">
                            <div class="flex flex-col mb-2">
                                <FloatLabel>
                                    <InputText name="email" type="text" inputId="email_label" class="w-full" />
                                    <label for="email_label">Email</label>
                                </FloatLabel>
                                <Message v-if="$form.email?.invalid" severity="error" size="small" variant="simple">{{ $form.email.error?.message }}</Message>
                                <Message v-if="serverErrors.email" severity="error" size="small" variant="simple">{{ serverErrors.email }}</Message>
                            </div>
                            <div class="flex flex-col">
                                <FloatLabel>
                                    <Password name="password"  fluid inputId="password_label" class="w-full" toggleMask :feedback="false" />
                                    <label for="password_label">Password</label>
                                </FloatLabel>
                                <template v-if="$form.password?.invalid">
                                    <Message v-for="(error, index) of $form.password.errors" :key="index" severity="error" size="small" variant="simple">{{ error.message }}</Message>
                                </template>
                                <Message v-if="serverErrors.password" severity="error" size="small" variant="simple">{{ serverErrors.password }}</Message>
                            </div>
                            <div class="flex justify-between">
                                <div class="flex items-center gap-2">
                                    <Checkbox inputId="remember_me" name="remember" binary />
                                    <label for="remember_me"> Remember me </label>
                                </div>
                                <div>
                                    <Button as="a" variant="link" href="/forgot-password" class="text-sm" >Forgot password ?</Button>
                                </div>
                            </div>
                            <div class="flex justify-end mt-5">
                                <Button type="submit" raised class="action-button !py-1 !px-3 sm:!py-2 sm:!px-5 !rounded-md">Login</Button>
                            </div>
                            <InputText name="_token" type="hidden" />
                        </Form>
                    </div>
                </template>
                <template #footer>
                </template>
            </Card>
        </div>
    </div>
</template>
