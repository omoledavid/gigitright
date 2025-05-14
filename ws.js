const { spawn } = require('child_process');
const server = spawn('php', ['artisan', 'reverb:start'], {
  detached: true,
  stdio: 'ignore'
});
server.unref();
process.exit(0);