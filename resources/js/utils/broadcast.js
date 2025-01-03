import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios';

export const initChatBroadcasting = channels => {
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
    console.log(channels);

    for (const channel of channels) {
        console.log(channel.id);
        laravelEcho.private(`chat-channel.${channel.id}`)
        .listen('MessageSent', (e) => {
            console.log(e, channel.id);
        });
    }

}
