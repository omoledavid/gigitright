cat > /home/gigitjgq/api.gigitright.com/manage-reverb.sh << 'EOL'
#!/bin/bash

# Path to your Laravel application
APP_PATH="/home/gigitjgq/api.gigitright.com"
LOG_FILE="$APP_PATH/storage/logs/reverb-manager.log"

# Function to log messages
log_message() {
    echo "[$(date)] $1" >> $LOG_FILE
}

# Go to app directory
cd $APP_PATH

# Count how many Reverb processes are running
REVERB_COUNT=$(ps aux | grep -v grep | grep "artisan reverb:start" | wc -l)

log_message "Found $REVERB_COUNT Reverb processes running"

# If more than one, kill all and restart
if [ $REVERB_COUNT -gt 1 ]; then
    log_message "Multiple Reverb instances detected. Killing all..."
    pkill -f "artisan reverb:start"
    sleep 2
    REVERB_COUNT=0
fi

# If none are running, start one
if [ $REVERB_COUNT -eq 0 ]; then
    # Make sure ports aren't in use
    for PORT in 6002 6005 6007 8080 12345; do
        if lsof -i:$PORT >/dev/null 2>&1; then
            log_message "Killing process on port $PORT"
            kill -9 $(lsof -t -i:$PORT) 2>/dev/null
        fi
    done
    
    sleep 1
    
    # Start Reverb
    log_message "Starting Reverb..."
    nohup php artisan reverb:start --host=127.0.0.1 --port=12345 >> $LOG_FILE 2>&1 &
    
    # Verify it started
    sleep 3
    if ps aux | grep -v grep | grep "artisan reverb:start" > /dev/null; then
        log_message "Reverb started successfully"
    else
        log_message "Failed to start Reverb"
    fi
else
    log_message "Reverb is already running properly"
fi
EOL

# Make it executable
chmod +x /home/gigitjgq/api.gigitright.com/manage-reverb.sh