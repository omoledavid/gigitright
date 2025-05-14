import Echo from 'laravel-echo';
import { io } from 'socket.io-client';

window.io = io;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    host: window.location.hostname + ':6002',
});
