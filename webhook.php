<?php

require_once 'settings.php';

// Get headers (case-insensitive)
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$received_secret = $headers['x-webhook-secret'] ?? '';

// Validate webhook secret
if ($received_secret !== $webhook_secret) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized', 'message' => 'Invalid or missing webhook secret']);
    exit;
}

// Parse JSON payload
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid JSON payload']);
    exit;
}

// Extract parameters
$action = $data['action'] ?? '';
$brightness = $data['brightness'] ?? 31;
$image_base64 = $data['image'] ?? null;

// Validate action
if (!in_array($action, ['display', 'kill'], true)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid action. Use: display or kill']);
    exit;
}

// Validate brightness
$brightness = max(1, min(99, (int)$brightness));

// Process action
if ($action === 'kill') {
    kill_process();
    rmimage();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'action' => 'kill']);
    exit;
}

// Action: display
if (!$image_base64) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Bad Request', 'message' => 'Missing image data for display action']);
    exit;
}

// Decode and save base64 image
$image_data = base64_decode($image_base64, true);
if ($image_data === false) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid base64 image data']);
    exit;
}

// Kill existing display and clean up
kill_process();
rmimage();

// Save the image
$output_file = 'ha_media_artwork.new.jpg';
if (file_put_contents($output_file, $image_data) === false) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to save image']);
    exit;
}

// Display the image
display_image($folder, $brightness);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'action' => 'display',
    'brightness' => $brightness,
    'image_size' => strlen($image_data)
]);
