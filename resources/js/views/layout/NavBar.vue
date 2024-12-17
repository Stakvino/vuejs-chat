<script setup>
import { Menubar, Button, Avatar, TieredMenu } from 'primevue';
import { ref, onMounted, watchEffect } from "vue";
import { RouterLink, useRouter } from 'vue-router';
import { useAppStore } from '@/stores/useApp';
import { useAuthStore } from '@/stores/useAuth';
import { storeToRefs } from 'pinia';
import axios from 'axios';

const { setNavIsReady } = useAppStore();
const { setIsAuth, setAuthUser } = useAuthStore();
const authStore = useAuthStore();
const { isAuth } = storeToRefs(authStore);

onMounted(() => { setNavIsReady(true); });

const router = useRouter();
const appStore = useAppStore();
const { currentRouteName } = storeToRefs(appStore);

const NavMenuItems = ref([
    {
        label: 'Home',
        icon: 'pi pi-home',
        command: () => {
            router.push('/');
        },
        class: currentRouteName.value === 'home' ? 'active' : null,
        meta: {
            name: 'home'
        }
    },
    {
        label: 'Chat',
        icon: 'pi pi-comments',
        command: () => {
            router.push('/chat');
        },
        class: currentRouteName.value === 'chat' ? 'active' : null,
        meta: {
            name: 'chat'
        }
    },
]);

watchEffect(() => {
    console.log(currentRouteName.value);
    NavMenuItems.value.forEach(NavMenuItem => {
        NavMenuItem.class = currentRouteName.value === NavMenuItem.meta.name ? 'active' : null
    })

})

const profileMenu = ref();
const profileItems = ref([
    {
        label: 'My Profile',
        icon: 'pi pi-user text-purple-500',
        command: () => {
            router.push('/');
        }
    },
    {
        separator: true
    },
    {
        label: 'Logout',
        icon: 'pi pi-sign-out text-red-500',
        command: () => {
            axios.post('/logout', {})
            .then(response => {
                const responseData = response['data'];
                if ( responseData['success'] && responseData['redirect'] ) {
                    setIsAuth(false);
                    setAuthUser(null);
                    router.push({ path: responseData['redirect'] })
                }
                else {
                    // error message
                }
            })
            .catch(e => console.log('catch error response', e))
        }
    }
])
const toggleProfileMenu = (event) => {
    profileMenu.value.toggle(event);
};
</script>


<template>
    <div class="card shadow-md">

        <div id="navbar" class="navbar md:mx-5">
            <Menubar :model="NavMenuItems">
                <template #start>
                    <RouterLink to="/">
                        <div class="main-logo mr-5">
                            <img src="/images/logo.png" width="50" alt="website logo">
                        </div>
                    </RouterLink>
                </template>
                <template #end>
                    <div v-if="isAuth">
                        <Avatar size="large" class="cursor-pointer" @click="toggleProfileMenu" aria-controls="profile_menu" image="https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png" alt="user profile pic" shape="circle" />
                        <TieredMenu ref="profileMenu" id="profile_menu" :model="profileItems" popup />
                    </div>
                    <div class="py-2" v-else>
                        <RouterLink to="/register">
                            <Button severity="secondary" class="action-button mr-3 !py-1 !px-3 sm:!py-2 sm:!px-5" raised>Register</Button>
                        </RouterLink>
                        <RouterLink to="/login">
                            <Button as="a" class="action-button !py-1 !px-3 sm:!py-2 sm:!px-5" raised>Login</Button>
                        </RouterLink>
                    </div>
                </template>
            </Menubar>
        </div>
    </div>
</template>

<style>
#navbar .p-menubar-item.active .p-menubar-item-content {
    color: white;
    background-color: #5dbea3;
}
#navbar .p-menubar-item.active .p-menubar-item-content .p-menubar-item-icon {
    color: white;
}

#navbar .p-menubar-item-label,
#navbar .p-menubar-item-icon {
    font-weight: bold;
}
#profile_menu_list li a {
    padding: 14px 12px;
}
</style>
