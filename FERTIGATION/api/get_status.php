<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "smartfarm");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit();
}

/* Fetch controller state */
$result = $conn->query("SELECT * FROM fertigation_control WHERE id = 1");
$state = $result ? $result->fetch_assoc() : null;

if (!$state) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Controller row missing'
    ]);
    exit();
}

/* Heartbeat check (ESP online if < 10s) */
$lastHeartbeatTs = $state['last_heartbeat']
    ? strtotime($state['last_heartbeat'])
    : 0;

$now = time();
$espOnline = ($now - $lastHeartbeatTs) < 10;

/* Response */
$response = [
    'success' => true,

    // ESP status
    'esp_online' => $espOnline,
    'esp_mode' => $state['esp_mode'],
    'esp_pump_state' => $state['esp_pump_state'],
    'seconds_since_heartbeat' => $lastHeartbeatTs
        ? ($now - $lastHeartbeatTs)
        : null,

    // Desired / server-side state
    'mode' => $state['mode'], // manual | scheduled
    'desired_pump_state' => $state['desired_pump_state'],

    // Schedule info
    'schedule_time' => $state['schedule_time'],
    'schedule_duration_minutes' => $state['schedule_duration_minutes'],

    // Timestamps
    'last_heartbeat' => $state['last_heartbeat'],
    'last_updated' => $state['last_updated']
];

echo json_encode($response);
$conn->close();
