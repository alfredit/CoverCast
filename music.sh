#!/bin/bash

# Define the directory where the script and viewer reside
UTILS_DIR="/CoverCast"
IMAGE_FILE="ha_media_artwork.jpg"
VIEWER_EXEC="led-image-viewer" # Just the executable name
PHP_SCRIPT="get_image.php"
LOG_FILE="/tmp/music_cron.log" # Optional: Log output for debugging

# --- Function for Logging ---
log_msg() {
  echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# --- Main Script ---
log_msg "--- Starting music.sh cron job ---"

# Change to the correct directory. Exit if it fails.
cd "$UTILS_DIR" || { log_msg "ERROR: Failed to cd into $UTILS_DIR"; exit 1; }
log_msg "Changed directory to $UTILS_DIR"

# Kill any previously running led-image-viewer instances
# Use 'pkill -9 -f' to match the command name, which is usually robust enough.
# Redirecting output to log file
log_msg "Attempting to kill previous $VIEWER_EXEC processes..."
pkill -f "$VIEWER_EXEC" >> "$LOG_FILE" 2>&1
# Add a small delay to allow the process to terminate cleanly
sleep 0.5
log_msg "Kill command executed."

# Remove the old image (optional, php script might just overwrite)
rm -f "$IMAGE_FILE" # -f suppresses errors if file doesn't exist

# Run the PHP script to download the new image
log_msg "Running PHP script: $PHP_SCRIPT"
php "$PHP_SCRIPT" >> "$LOG_FILE" 2>&1
PHP_EXIT_CODE=$? # Capture exit code of php script
if [ $PHP_EXIT_CODE -ne 0 ]; then
    log_msg "ERROR: PHP script failed with exit code $PHP_EXIT_CODE."
    # Decide if you want to exit or try starting viewer with old image
    # For safety, let's exit if the download fails
    exit 1
else
    log_msg "PHP script completed successfully."
fi

# Check if the image file exists before trying to display it
if [ ! -f "$IMAGE_FILE" ]; then
    log_msg "ERROR: Image file '$IMAGE_FILE' not found after download attempt. Cannot start viewer."
    exit 1
fi

# Start the new led-image-viewer instance
# Corrected the typo: Assuming you meant --led-brightness, adjust value as needed
# so the script itself can exit cleanly for cron.
log_msg "Starting new $VIEWER_EXEC instance..."
sudo /script/rpi-rgb-led-matrix/utils/led-image-viewer -C -f -w3 "$IMAGE_FILE" --led-rows=64 --led-cols=64 --led-brightness=40 --led-daemon >> "$LOG_FILE" 2>&1

log_msg "--- music.sh cron job finished ---"

exit 0
