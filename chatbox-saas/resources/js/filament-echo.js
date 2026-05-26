import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;

if (typeof reverbKey === 'string' && reverbKey.length > 0) {
    window.Pusher = Pusher;

    const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';
    const forceTLS = scheme === 'https';
    const rawPort = import.meta.env.VITE_REVERB_PORT;
    const port = rawPort !== undefined && rawPort !== '' ? Number(rawPort) : forceTLS ? 443 : 80;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
        wsPort: port,
        wssPort: port,
        forceTLS,
        enabledTransports: ['ws', 'wss'],
    });
} else if (typeof pusherKey === 'string' && pusherKey.length > 0) {
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
        forceTLS: true,
    });
}
