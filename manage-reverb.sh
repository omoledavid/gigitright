# Path to your Laravel application
APP_PATH="/home/gigitjgq/api.gigitright.com"

# Go to app directory
cd $APP_PATH

# Check if Reverb is already running
if ps aux | grep -v grep | grep "artisan reverb:start" > /dev/null; then
    # Reverb is running, do nothing
    echo "Reverb is already running"
    exit 0
else
    # Kill any stuck processes on port 12345
    if lsof -i:12345 >/dev/null 2>&1; then
        kill -9 $(lsof -t -i:12345)
        sleep 1
    fi
    
    # Start Reverb with specific port
    php artisan reverb:start --host=127.0.0.1 --port=12345 >> $APP_PATH/storage/logs/reverb-cron.log 2>&1 &
    echo "Started Reverb at $(date)" >> $APP_PATH/storage/logs/reverb-cron.log
fi
EOL

# Make it executable
chmod +x /home/gigitjgq/api.gigitright.com/manage-reverb.sh