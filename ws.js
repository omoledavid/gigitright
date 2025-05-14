const net = require('net');

const port = 6001; // or 6005

const server = net.createServer();

server.once('error', function (err) {
    if (err.code === 'EADDRINUSE') {
        console.log(`Port ${port} is already in use. Reverb is likely running.`);
    } else {
        console.error(err);
    }
});

server.once('listening', function () {
    server.close();

    const { spawn } = require('child_process');
    const reverb = spawn('php', ['artisan', 'reverb:start'], {
        detached: true,
        stdio: 'ignore'
    });
    reverb.unref();
    console.log(`Reverb started on port ${port}`);
});

server.listen(port);
