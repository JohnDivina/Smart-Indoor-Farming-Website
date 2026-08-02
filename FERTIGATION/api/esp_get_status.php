<?php
/*
 * ESP32-ONLY Status Endpoint
 * 
 * NO SESSION AUTHENTICATION - ESP32 cannot use PHP sessions
 * This endpoint is separate from the website's get_status.php
 * 
 * ESP32 should call this endpoint to get desired state
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/esp32_auth.php';
verify_esp32_auth();

require_once __DIR__ . '/../../database.php';

/* Fetch controller state (always id = 1) */
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

/* Response - only what ESP32 needs */
$response = [
    'success' => true,
    
    // What ESP32 should do
    'desired_pump_state' => $state['desired_pump_state'],
    'mode' => $state['mode'],
    
    // Schedule info (if in scheduled mode)
    'schedule_time' => $state['schedule_time'],
    'schedule_duration_minutes' => (int)$state['schedule_duration_minutes'],
    'config_version'            => (int)$state['config_version'],
    
    // Timestamp for ESP32 logging
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response);
// Connection handled by shared database.php
?>
