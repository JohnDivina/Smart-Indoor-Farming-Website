<?php
header('Content-Type: application/json');

// You'll need to update these IP addresses when you connect your microcontrollers
$esp32_ip_set1 = "http://192.168.0.114"; // Fertigation Set 1 ESP32 IP
$esp32_ip_set2 = "http://192.168.0.118"; // Fertigation Set 2 ESP32 IP

// Initialize cURL session for better error handling
$ch = curl_init();

// Try to get status from both microcontrollers
$status_set1 = 'disconnected';
$status_set2 = 'disconnected';
$relay_set1 = 'unknown';
$relay_set2 = 'unknown';

// Check Set 1
curl_setopt($ch, CURLOPT_URL, "$esp32_ip_set1/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5-second timeout
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

$response_set1 = curl_exec($ch);
$http_code_set1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_set1 = ($response_set1 === false) ? curl_error($ch) : '';

if ($response_set1 !== false && $http_code_set1 === 200) {
    // Consider device reachable => connected
    $status_set1 = 'connected';
    $data_set1 = json_decode($response_set1, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($data_set1)) {
        if (isset($data_set1['status'])) {
            $s = strtolower($data_set1['status']);
            if (in_array($s, ['disconnected','offline'])) {
                $status_set1 = 'disconnected';
            } else {
                $status_set1 = 'connected';
            }
        }
        if (isset($data_set1['relay'])) {
            $relay_set1 = strtolower($data_set1['relay']);
        }
    }
} else {
    // Fallback: try device root to see if web server is up
    curl_setopt($ch, CURLOPT_URL, $esp32_ip_set1);
    $resp_root1 = curl_exec($ch);
    $code_root1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($resp_root1 !== false && $code_root1 === 200) {
        $status_set1 = 'connected';
    }
}

// Check Set 2
curl_setopt($ch, CURLOPT_URL, "$esp32_ip_set2/status");
$response_set2 = curl_exec($ch);
$http_code_set2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_set2 = ($response_set2 === false) ? curl_error($ch) : '';

if ($response_set2 !== false && $http_code_set2 === 200) {
    // Consider device reachable => connected
    $status_set2 = 'connected';
    $data_set2 = json_decode($response_set2, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($data_set2)) {
        if (isset($data_set2['status'])) {
            $s = strtolower($data_set2['status']);
            if (in_array($s, ['disconnected','offline'])) {
                $status_set2 = 'disconnected';
            } else {
                $status_set2 = 'connected';
            }
        }
        if (isset($data_set2['relay'])) {
            $relay_set2 = strtolower($data_set2['relay']);
        }
    }
} else {
    // Fallback: try device root to see if web server is up
    curl_setopt($ch, CURLOPT_URL, $esp32_ip_set2);
    $resp_root2 = curl_exec($ch);
    $code_root2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($resp_root2 !== false && $code_root2 === 200) {
        $status_set2 = 'connected';
    }
}

curl_close($ch);

// Determine overall status
if ($status_set1 === 'connected' || $status_set2 === 'connected') {
    $overall_status = 'connected';
    // If either is on, consider the system on
    $overall_relay = ($relay_set1 === 'on' || $relay_set2 === 'on') ? 'on' : 'off';
    $message = 'Fertigation system operational';
} else {
    $overall_status = 'disconnected';
    $overall_relay = 'unknown';
    $message = 'Unable to reach fertigation microcontrollers';
}

// Create relay status for display
$relay_status = [
    "relay1" => ($relay_set1 === 'on'),
    "relay2" => ($relay_set1 === 'on'),
    "relay3" => ($relay_set2 === 'on'),
    "relay4" => ($relay_set2 === 'on')
];

echo json_encode([
    "status" => $overall_status,
    "relay" => $overall_relay,
    "message" => $message,
    "set1" => [
        "status" => $status_set1,
        "relay" => $relay_set1,
        "http_code" => $http_code_set1,
        "error" => $error_set1
    ],
    "set2" => [
        "status" => $status_set2,
        "relay" => $relay_set2,
        "http_code" => $http_code_set2,
        "error" => $error_set2
    ],
    "relayStatus" => $relay_status
]);
?>
