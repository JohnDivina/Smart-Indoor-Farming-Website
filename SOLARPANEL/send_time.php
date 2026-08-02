<?php
header("Content-Type: application/json");
date_default_timezone_set("Asia/Manila");

$time = date("Y-m-d H:i:s");

// If ESP32 is sending POST data (connection status + relay)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? 'unknown';
    $relay = $_POST['relay'] ?? 'unknown';

    echo json_encode([
        "status" => $status,
        "relay" => $relay,
        "time" => $time
    ]);
    exit;
}

// If frontend (JavaScript) is fetching via GET
$esp32_ip = "http://192.168.0.102";

$relay = 'unknown';
$status = 'disconnected';

try {
    $relay = @file_get_contents("$esp32_ip/status");
    if ($relay !== false) {
        $status = 'connected';
        $relay = trim($relay); // Expecting 'Relay is ON' or 'Relay is OFF'

        if (stripos($relay, 'ON') !== false) {
            $relay = 'on';
        } elseif (stripos($relay, 'OFF') !== false) {
            $relay = 'off';
        } else {
            $relay = 'unknown';
        }
    }
} catch (Exception $e) {
    $status = 'disconnected';
}

echo json_encode([
    "status" => $status,
    "relay" => $relay,
    "time" => $time
]);
?>
