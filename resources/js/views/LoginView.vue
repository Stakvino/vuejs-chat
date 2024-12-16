<script setup>
    import { Card, Button, InputText, Toast, Message, Checkbox, Password, FloatLabel } from 'primevue';
    import { ref } from 'vue';
    import { Form } from '@primevue/forms';
    import { zodResolver } from '@primevue/forms/resolvers/zod';
    import { useToast } from "primevue/usetoast";
    import { z } from 'zod';

    const toast = useToast();
    const initialValues = ref({
        email: '',
        password: '',
        remember: false
    });

    const resolver = ref(zodResolver(
        z.object({
            email: z.string().min(1, { message: 'Email is required.' }).email({ message: 'Invalid email address.' }),
            password: z.string().min(1, { message: 'Password is required.' }).min(8, {message: 'Password need to be atleast 8 charachters.'}),
            remember: z.boolean()
        })
    ));

    const onFormSubmit = ({ valid }) => {
        if (valid) {
            toast.add({ severity: 'success', summary: 'Form is submitted.', life: 3000 });
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
                            </div>
                            <div class="flex flex-col">
                                <FloatLabel>
                                    <Password name="password"  fluid inputId="password_label" class="w-full" toggleMask />
                                    <label for="password_label">Password</label>
                                </FloatLabel>
                                <template v-if="$form.password?.invalid">
                                    <Message v-for="(error, index) of $form.password.errors" :key="index" severity="error" size="small" variant="simple">{{ error.message }}</Message>
                                </template>
                            </div>
                            <div class="flex justify-between">
                                <div class="flex items-center gap-2">
                                    <Checkbox inputId="remember_me" name="remember" binary />
                                    <label for="remember_me"> Remember me </label>
                                </div>
                                <div>
                                    <Button variant="link" class="text-sm" >Forgot password ?</Button>
                                </div>
                            </div>
                            <div class="flex justify-end mt-5">
                                <Button type="submit" raised class="action-button !py-1 !px-3 sm:!py-2 sm:!px-5 !rounded-md">Login</Button>
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
