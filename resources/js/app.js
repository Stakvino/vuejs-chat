import 'primeicons/primeicons.css'

import { createApp } from 'vue';
import app from '@/components/app.vue';
import PrimeVue from 'primevue/config';
import { definePreset } from '@primevue/themes';
import Aara from '@primevue/themes/aura';
import router from '@/router';
import ToastService from 'primevue/toastservice';
import { createPinia } from 'pinia';

/*
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios';

window.Pusher = Pusher;

const laravelEcho = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    wsHost: import.meta.env.VITE_PUSHER_HOST,
    wsPort: import.meta.env.VITE_PUSHER_PORT,
    wssPort: import.meta.env.VITE_PUSHER_PORT,
    forceTLS: false,
    encrypted: true,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    cluster:import.meta.env.VITE_PUSHER_APP_CLUSTER,
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                axios.post('/api/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                })
                .then(response => {
                    callback(false, response.data);
                })
                .catch(error => {
                    callback(true, error);
                });
            }
        };
    },
});

const orderId = 1;
laravelEcho.private(`user-order.${orderId}`)
.listen('OrderShipmentStatusUpdated', (e) => {
    console.log(e, 666);
});

laravelEcho.channel(`test`)
.listen('TestEvent', (e) => {
    console.log(e, 123);
});
*/
const MyPreset = definePreset(Aara, {
    semantic: {
        colorScheme: {
            light: {
                primary: {
                    color: '#a881af',
                    hoverColor: '#80669d',
                    activeColor: '#80669d'
                },
            }
        }
    }
});

const pinia = createPinia();


createApp(app)
.use(
    PrimeVue, {
        theme: {
            preset: MyPreset,
            options: {
                cssLayer: {
                    name: 'primevue',
                    order: 'tailwind-base, primevue, tailwind-utilities'
                }
            }
        }
    }
)
.use(pinia)
.use(router)
.use(ToastService)
.mount('#app')
