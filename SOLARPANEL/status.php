<?php
// SOLARPANEL/status.php in smart-farm-dashboard
header('Content-Type: application/json');

$esp32_ip = "http://192.168.0.102"; // Update with your ESP32 IP

// Initialize cURL session for better error handling
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$esp32_ip/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2); // 2-second timeout
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $http_code !== 200) {
    // Try alternative endpoint for connection check
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$esp32_ip/connection_check");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    $connection_check = curl_exec($ch);
    curl_close($ch);
    
    if ($connection_check !== false) {
        // ESP32 is reachable but status endpoint failed
        echo json_encode([
            "status" => "connected",
            "relay" => "unknown",
            "message" => "ESP32 reachable but status unavailable"
        ]);
    } else {
        // ESP32 is completely unreachable
        echo json_encode([
            "status" => "disconnected",
            "relay" => "unknown",
            "message" => "Unable to reach ESP32"
        ]);
    }
    exit;
}

// Process successful response
$data = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE && isset($data['status']) && isset($data['relay'])) {
    echo json_encode([
        "status" => strtolower($data['status']),
        "relay" => strtolower($data['relay']),
        "message" => "Data fetched successfully"
    ]);
} else {
    echo json_encode([
        "status" => "unknown",
        "relay" => "unknown",
        "message" => "Invalid data received from ESP32"
    ]);
}
?>
