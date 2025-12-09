import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
Echo.channel('forum')
    .listen('ForumMessageCreated', (e) => {
        let chatBox = document.getElementById("chat-box");
        chatBox.innerHTML += `<div class="flex">
            <div class="bg-white shadow px-3 py-2 rounded-lg">
                <p><b>${e.message.user.name}:</b> ${e.message.message ?? ''}</p>
            </div>
        </div>`;
        chatBox.scrollTop = chatBox.scrollHeight;
    });

    import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

