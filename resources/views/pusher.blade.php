<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.3.4/axios.min.js"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="w-full max-w-lg bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-xl font-bold text-center">Real-Time Chat</h2>
    <div id="messages" class="h-60 overflow-auto p-2 border border-gray-300 rounded-lg mt-4 bg-gray-50"></div>

    <input type="hidden" id="conversation_id" value="1"> {{-- Change this based on your database --}}
    <textarea id="message" class="w-full mt-4 p-2 border border-gray-300 rounded-lg" rows="2" placeholder="Type a message..."></textarea>
    <button onclick="sendMessage()" class="w-full mt-2 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">Send</button>
</div>

<script>

    // Set up Pusher
    // Set up Pusher
    Pusher.logToConsole = true;
    const pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
        cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
        encrypted: true,
        authEndpoint: "{{ url('/pusher/auth') }}",  // Specify the auth endpoint for private channels
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }
    });
    console.log(pusher)

    const conversationId = document.getElementById("conversation_id").value;
    const messagesDiv = document.getElementById("messages");

    // Fetch initial messages
    axios.get(`{{ url('/conversation/${conversationId}/messages') }}`)
        .then(response => {
            console.log('got data');
            response.data.forEach(message => {
                const msg = document.createElement('div');
                msg.classList.add('p-2', 'bg-gray-200', 'rounded-lg', 'my-1');
                msg.textContent = message.message;
                messagesDiv.appendChild(msg);
            });
            // Scroll to the bottom
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        })
        .catch(error => {
            console.error("Error fetching messages:", error);
        });

    // Subscribe to the private channel
    const channel = pusher.subscribe(`chat.${conversationId}`);
    channel.bind("MessageSent", function (data) {
        console.log('event received');
        const msg = document.createElement('div');
        msg.classList.add('p-2', 'bg-gray-200', 'rounded-lg', 'my-1');
        msg.textContent = data.message.message;
        messagesDiv.appendChild(msg);
        // Scroll to the bottom
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    });

    // Send a message
    function sendMessage() {
        const message = document.getElementById("message").value;
        if (!message.trim()) return;

        axios.post("{{ route('send.message') }}", {
            conversation_id: conversationId,
            message: message
        }, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => {
                document.getElementById("message").value = "";
                const msg = document.createElement('div');
                msg.classList.add('p-2', 'bg-blue-200', 'rounded-lg', 'my-1', 'text-right');
                msg.textContent = message;
                messagesDiv.appendChild(msg);
                // Scroll to the bottom
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            })
            .catch(error => console.error(error));
    }

</script>
</body>
</html>
