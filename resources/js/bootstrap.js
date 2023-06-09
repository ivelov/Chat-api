import _ from 'lodash';
window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from '@ably/laravel-echo';
// import * as Ably from 'ably';

// window.Ably = Ably; // make globally accessible to Echo
// window.Echo = new Echo({
//     broadcaster: 'ably',
//     authEndpoint: '/broadcasting/auth',
//     echoMessages: true, // self-echo for published message is set to false internally.
//     queueMessages: true, // default: true, maintains queue for messages to be sent.
//     disconnectedRetryTimeout: 15000, // Retry connect after 15 seconds when client gets disconnected
// });

// window.Echo.connector.ably.connection.on(stateChange => {
//     if (stateChange.current === 'connected') {
//         console.log('connected to ably server');
//     }
// });