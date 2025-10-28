
import axios from 'axios';
window.axios = axios;
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY, // Use Vite env vars
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER, // Use Vite env vars
    forceTLS: true // Or false if not using HTTPS locally
    // You might need wsHost and wsPort for local dev without TLS
    // wsHost: window.location.hostname,
    // wsPort: 6001, // Default Reverb/Soketi port, adjust if needed
    // disableStats: true,
    // enabledTransports: ['ws', 'wss'],
});