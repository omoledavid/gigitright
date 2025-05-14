cat > /home/gigitjgq/api.gigitright.com/manage-reverb.sh << 'EOL'
#!/bin/bash

# Path to your Laravel application
APP_PATH="/home/gigitjgq/api.gigitright.com"
LOG_FILE="$APP_PATH/storage/logs/reverb-manager.log"
PORT=6001  # Set the port Reverb will use

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
    for P in 6002 6005 6007 8080 $PORT; do
        if lsof -i:$P >/dev/null 2>&1; then
            log_message "Killing process on port $P"
            kill -9 $(lsof -t -i:$P) 2>/dev/null
        fi
    done

    sleep 1

    # Start Reverb
    log_message "Starting Reverb on port $PORT..."
    nohup php artisan reverb:start --host=127.0.0.1 --port=$PORT >> $LOG_FILE 2>&1 &

    # Verify it started
    sleep 3
    if ps aux | grep -v grep | grep "artisan reverb:start" > /dev/null; then
        log_message "Reverb started successfully on port $PORT"
    else
        log_message "Failed to start Reverb on port $PORT"
    fi
else
    log_message "Reverb is already running properly on port $PORT"
fi
EOL

# Make it executable
chmod +x /home/gigitjgq/api.gigitright.com/manage-reverb.sh