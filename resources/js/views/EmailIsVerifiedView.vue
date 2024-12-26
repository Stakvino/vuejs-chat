<script setup>
import { Card, Button } from 'primevue';
import Divider from 'primevue/divider';
import { onMounted } from 'vue';
import { useAppStore } from '@/stores/useApp'

const { setContentIsReady } = useAppStore();
onMounted(() => setContentIsReady(true));

</script>

<template>
    <div class="mt-10">
        <div class="auth-container flex flex-col items-center">
            <div>
                <img width="100" src="/images/logo.png" alt="website logo">
            </div>
            <Card>
                <template #title>
                    <p class="font-bold">Your Email has been verified</p>
                </template>
                <template #content>

                    <Divider class="border my-2" />

                    <div class="flex justify-center mt-4">
                        <Button as="a" rounded class="w-full" href="/chat" raised>Go to chat</Button>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
