import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/HomeView.vue'
import ChatView from '@/views/ChatView.vue'
import RegisterView from '@/views/RegisterView.vue'
import LoginView from '@/views/LoginView.vue'
import { useAuthStore } from '@/stores/useAuth'
import { useAppStore } from '@/stores/useApp';
import { storeToRefs } from 'pinia';
import axios from 'axios'

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
            middleware: 'auth',
        },
    }
    ,{
        path: '/register',
        name: 'register',
        component: RegisterView,
        meta: {
            middleware: 'guest',
        },
    }
    ,{
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: {
            middleware: 'guest',
        },
    }
  ]
})

router.beforeEach(async (to, from) => {
    const { setIsAuth } = useAuthStore()
    let isAuth = false;

    await axios.get('/api/auth-check')
    .then(response => {
        isAuth = !!response.data
        setIsAuth(isAuth);
    })
    .catch(e => console.log('catch error response', e))

    const { setCurrentRouteName } = useAppStore();
    setCurrentRouteName(to.name);

    const middleware = to.meta.middleware;
    if ( !isAuth && middleware === 'auth' ) {
      return { name: 'login' }
    }
    if ( isAuth && middleware === 'guest' ) {
        return { name: 'home' }
    }
})

export default router
