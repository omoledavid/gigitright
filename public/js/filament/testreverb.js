// Note: Using the wss protocol for secure connections
const socket = new WebSocket('wss://gigitright.test:8080/app/UUIdf34-davkeojo-key-12345');

socket.onopen = function(e) {
  console.log("Connection established");
};

socket.onmessage = function(event) {
  console.log(`Data received: ${event.data}`);
};

socket.onclose = function(event) {
  console.log('Connection closed');
};