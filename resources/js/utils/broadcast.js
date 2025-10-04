import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios';

export const initChatBroadcasting = () => {
    window.Pusher = Pusher;
    const laravelEcho = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT,
        wssPort: import.meta.env.VITE_REVERB_PORT,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        // forceTLS: false,
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
                        console.log(error);
                        callback(true, error);
                    });
                }
            };
        },
    });

    return laravelEcho;
}
