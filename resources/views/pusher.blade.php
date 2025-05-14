<!DOCTYPE html>
<html>
<head>
    <title>Reverb WebSocket Test</title>
</head>
<body>
    <h2 id="status">Connecting...</h2>
    <div id="messages"></div>

    <script>
        const statusDiv = document.getElementById('status');
        const messagesDiv = document.getElementById('messages');

        const socket = new WebSocket(
            'wss://api.gigitright.com:6001/app/local?protocol=7&client=js&version=1.1.0'
        );

        socket.onopen = function (e) {
            statusDiv.textContent = "Connected!";
            console.log("Connection established");

            // Subscribe to a channel manually
            const payload = {
                event: "pusher:subscribe",
                data: {
                    auth: "",
                    channel: "testing"
                }
            };
            socket.send(JSON.stringify(payload));
        };

        socket.onmessage = function (event) {
            const messageEl = document.createElement('p');
            messageEl.textContent = `Received: ${event.data}`;
            messagesDiv.appendChild(messageEl);
            console.log(`Data received: ${event.data}`);
        };

        socket.onclose = function (event) {
            statusDiv.textContent = "Disconnected";
            console.log('Connection closed');
        };

        socket.onerror = function (error) {
            statusDiv.textContent = "Error occurred";
            console.error('WebSocket Error:', error);
        };
    </script>
</body>
</html>
