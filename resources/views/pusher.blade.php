<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pusher Private Channel Test</title>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        #messages {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            height: 300px;
            overflow-y: auto;
        }

        button {
            padding: 10px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .status {
            margin-top: 10px;
        }

        .connected {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>
    <h1>Pusher Private Channel Test</h1>
    <p>This page tests if Pusher private channels are correctly configured with your Laravel application.</p>

    <div>
        <label for="conversation">Conversation ID:</label>
        <input type="number" id="conversation" value="1" min="1">
        <button onclick="connectToChannel()">Connect to Channel</button>
    </div>

    <div class="status" id="status">Not connected</div>

    <h2>Messages:</h2>
    <div id="messages"></div>

    <script>
        // Enable pusher logging for debugging
        Pusher.logToConsole = true;

        let pusher;
        let channel;

        function connectToChannel() {
            const conversationId = document.getElementById('conversation').value;

            // Clean up existing connection if any
            if (pusher) {
                pusher.disconnect();
            }

            // Initialize Pusher
            var pusher = new Pusher('593a2d0d6072bde767e1', {
                cluster: 'us3',
                // Important for private channels
                authEndpoint: 'api/v1/pusher/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }
            });

            // Subscribe to the private channel
            channel = pusher.subscribe(`conversation.${conversationId}`);

            // Connection status
            pusher.connection.bind('connected', () => {
                document.getElementById('status').textContent = 'Connected to Pusher';
                document.getElementById('status').className = 'status connected';
            });

            pusher.connection.bind('disconnected', () => {
                document.getElementById('status').textContent = 'Disconnected from Pusher';
                document.getElementById('status').className = 'status';
            });

            // Channel subscription succeeded
            channel.bind('pusher:subscription_succeeded', () => {
                const messagesDiv = document.getElementById('messages');
                const messageElement = document.createElement('div');
                messageElement.textContent = `Successfully subscribed to conversation.${conversationId}`;
                messageElement.style.color = 'green';
                messagesDiv.appendChild(messageElement);
            });

            // Channel subscription error
            channel.bind('pusher:subscription_error', (error) => {
                const messagesDiv = document.getElementById('messages');
                const messageElement = document.createElement('div');
                messageElement.textContent = `Error subscribing to channel: ${JSON.stringify(error)}`;
                messageElement.style.color = 'red';
                messagesDiv.appendChild(messageElement);

                document.getElementById('status').textContent = 'Error connecting to channel';
                document.getElementById('status').className = 'status error';
            });

            // Listen for new messages
            channel.bind('new.message', (data) => {
                const messagesDiv = document.getElementById('messages');
                const messageElement = document.createElement('div');
                messageElement.innerHTML = `
                    <p><strong>${data.sender.name}:</strong> ${data.message}</p>
                    <small>${new Date(data.created_at).toLocaleTimeString()}</small>
                `;
                messagesDiv.appendChild(messageElement);
            });
        }
    </script>
</body>

</html>
