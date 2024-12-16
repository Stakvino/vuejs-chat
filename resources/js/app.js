import 'primeicons/primeicons.css'

import { createApp } from 'vue';
import app from './components/app.vue';
import PrimeVue from 'primevue/config';
import { definePreset } from '@primevue/themes';
import Aara from '@primevue/themes/aura';
import router from './router';
import ToastService from 'primevue/toastservice';

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

createApp(app).use(
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
).use(router).use(ToastService).mount('#app')
