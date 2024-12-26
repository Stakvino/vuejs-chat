import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '@/views/HomeView.vue';
import ChatView from '@/views/ChatView.vue';
import RegisterView from '@/views/RegisterView.vue';
import LoginView from '@/views/LoginView.vue';
import ForgotPasswordView from '@/views/ForgotPasswordView.vue';
import EmailIsVerifiedView from '@/views/EmailIsVerifiedView.vue';
import EmailVerificationView from '@/views/EmailVerificationView.vue';
import PasswordResetView from '@/views/PasswordResetView.vue';
import { useAuthStore } from '@/stores/useAuth';
import { useAppStore } from '@/stores/useApp';
import { storeToRefs } from 'pinia';
import axios from 'axios';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
        path: '/',
        name: 'home',
        component: HomeView
    },
    {
        path: '/chat',
        name: 'chat',
        component: ChatView,
        meta: {
            middleware: ['auth', 'verified'],
        },
    }
    ,{
        path: '/register',
        name: 'register',
        component: RegisterView,
        meta: {
            middleware: ['guest'],
        },
    }
    ,{
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: {
            middleware: ['guest'],
        },
    }
    ,{
        path: '/email/verify',
        name: 'email-verification',
        component: EmailVerificationView,
        meta: {
            middleware: ['auth', 'not-verified'],
        },
    }
    ,{
        path: '/email-is-verified',
        name: 'email-is-verified',
        component: EmailIsVerifiedView,
        meta: {
            middleware: ['auth'],
        },
    }
    ,{
        path: '/forgot-password',
        name: 'forgot-password',
        component: ForgotPasswordView,
        meta: {
            middleware: ['guest'],
        },
    },
    {
        path: '/password-reset/:token',
        name: 'password-reset',
        component: PasswordResetView,
        meta: {
            middleware: ['guest'],
        },
    }
  ]
})

router.beforeEach(async (to, from) => {
    const authStore = useAuthStore();
    const { setIsAuth, fetchAuthUser } = authStore;
    const { isEmailVerified } = storeToRefs(authStore);
    let isAuth = false;

    const { setCurrentRouteName } = useAppStore();
    setCurrentRouteName(to.name || null);

    await axios.get('/api/auth-check')
    .then(response => {
        isAuth = !!response.data;
        setIsAuth(isAuth || null);
    })
    .catch(e => console.log('catch error response', e))

    if (isAuth) {
        await fetchAuthUser();
    }

    const middleware = to.meta.middleware;
    if ( !middleware ) return;
    if ( !isAuth && middleware.includes('auth') ) {
      return { name: 'login' }
    }
    else if ( !isEmailVerified.value && middleware.includes('verified') ) {
        return { name: 'email-verification' }
    }
    // This middleware is used for people who want to access email verification page even tho they are already verified
    else if ( isEmailVerified.value && middleware.includes('not-verified') ) {
        return { name: 'email-is-verified' }
    }
    else if ( isAuth && middleware.includes('guest') ) {
        return { name: 'home' }
    }
})

export default router
