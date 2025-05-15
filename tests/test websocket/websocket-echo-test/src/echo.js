import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import axios from 'axios'

window.Pusher = Pusher

const echo = new Echo({
    broadcaster: 'pusher',
    key: 'local', // matches your `REVERB_APP_KEY` or Echo config
    wsHost: 'api.gigitright.com',
    wsPort: 6001,
    wssPort: 6001,
    forceTLS: true,
    encrypted: true,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    // authEndpoint (optional if using private channels):
    // authEndpoint: 'https://api.gigitright.com/broadcasting/auth',
    // auth: {
    //     headers: {
    //         Authorization: 'Bearer YOUR_TOKEN'
    //     }
    // }
})

export default echo
