<?php

//$ha_url = 'http://192.168.31.210:8123/api/media_player_proxy/media_player.spotify_alfredit';
$ha_url = 'http://192.168.31.210:8123/api/media_player_proxy/media_player.sejour';

$long_lived_access_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJjMjMzOTI3MGJjNjA0MDhkODQzOTRiOTE3OWQ0NTM1MSIsImlhdCI6MTc0Mzc5NzE4MiwiZXhwIjoyMDU5MTU3MTgyfQ.MReKGRGMXnnal3pvaoRQjew0GEfnHA7unBcRkifyi3o';
$output_file = 'ha_media_artwork.jpg';

$ch = curl_init();

if ($ch === false) {
    die("Error: Failed to initialize cURL session.\n");
}

curl_setopt($ch, CURLOPT_URL, $ha_url); // Set the URL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string instead of outputting it
curl_setopt($ch, CURLOPT_HEADER, false); // Don't include the header in the output string
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow any redirects
curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Set a timeout in seconds (adjust as needed)
curl_setopt($ch, CURLOPT_FAILONERROR, true); // Consider HTTP codes >= 400 as errors

// Set the Authorization header for Home Assistant API
$headers = [
    'Authorization: Bearer ' . $long_lived_access_token,
    'Content-Type: application/json' // Although getting an image, HA API often expects this
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute the cURL request
echo "Attempting to download image from: " . $ha_url . "\n";
$imageData = curl_exec($ch);

// Check for cURL errors during execution
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "cURL Error: " . $error_msg . "\n";
    echo "HTTP Status Code: " . $http_code . "\n";
    if ($http_code == 401) {
        echo "Authentication failed (HTTP 401). Check if your Long-Lived Access Token is correct and valid.\n";
    } elseif ($http_code == 404) {
        echo "Resource not found (HTTP 404). Check if the URL and entity ID are correct.\n";
    } else {
        echo "Could not connect to Home Assistant or other network error occurred.\n";
    }
} elseif ($imageData === false || empty($imageData)) {
    // Check if response is empty even if no direct curl error reported
     $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
     echo "Error: Request succeeded (HTTP " . $http_code . ") but received empty response. Is the media player currently playing something with artwork?\n";
} else {
    // Request was successful, $imageData contains the raw image data
    echo "Image data received successfully.\n";

    // Try to save the image data to the specified file
    if (file_put_contents($output_file, $imageData) !== false) {
        echo "Image successfully downloaded and saved to '" . $output_file . "'.\n";
    } else {
        // Get the last error for more details if file saving failed
        $error = error_get_last();
        echo "Error: Could not save the image to '" . $output_file . "'.\n";
        echo "Reason: " . ($error['message'] ?? 'Unknown file system error. Check directory permissions.') . "\n";
    }
}

// Close cURL session
curl_close($ch);

?>
