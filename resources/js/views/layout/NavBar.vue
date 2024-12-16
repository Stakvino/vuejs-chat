<script setup>
import { Menubar, Button } from 'primevue';
import { ref } from "vue";
import { RouterLink } from 'vue-router';
import { useRouter } from 'vue-router';

const router = useRouter();

const items = ref([
    {
        label: 'Home',
        icon: 'pi pi-home',
        command: () => {
            router.push('/');
        }
    },
    {
        label: 'Chat',
        icon: 'pi pi-comments',
        command: () => {
            router.push('/chat');
        }
    },
]);
</script>


<template>
    <div class="card shadow-md">
        <div class="navbar p-2 md:mx-5">
            <Menubar :model="items">
                <template #start>
                    <RouterLink to="/">
                        <div class="main-logo mr-5">
                            <img src="/images/logo.png" width="50" alt="website logo">
                        </div>
                    </RouterLink>
                </template>
                <template #end>
                    <RouterLink to="/register">
                        <Button severity="secondary" class="action-button mr-3 !py-1 !px-3 sm:!py-2 sm:!px-5" raised>Register</Button>
                    </RouterLink>
                    <RouterLink to="/login">
                        <Button as="a" class="action-button !py-1 !px-3 sm:!py-2 sm:!px-5" raised>Login</Button>
                    </RouterLink>
                </template>
            </Menubar>
        </div>
    </div>
</template>
