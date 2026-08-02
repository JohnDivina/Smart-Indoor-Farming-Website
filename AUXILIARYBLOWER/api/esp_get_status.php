<?php
/*
 * ESP32-ONLY Status Polling Endpoint — Auxiliary Blower
 *
 * NO SESSION AUTHENTICATION — ESP32 cannot use PHP sessions.
 * ESP32 polls this with HTTP GET to find out what state to run.
 *
 * Returns: desired_fan_state, mode, schedule_time, schedule_duration_minutes
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/esp32_auth.php';
verify_esp32_auth();

require_once __DIR__ . '/../../database.php';
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$result = $conn->query("SELECT * FROM fan_state WHERE id = 1");
$row    = $result ? $result->fetch_assoc() : null;

if (!$row) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Controller row missing']);
    $conn->close();
    exit();
}

echo json_encode([
    'success'                   => true,
    'desired_fan_state'         => $row['desired_fan_state'],          // "on" | "off"
    'mode'                      => $row['mode'],                       // "manual" | "scheduled" | "auto"
    'schedule_time'             => $row['schedule_time'],              // "HH:MM:SS" | null
    'schedule_stop_time'        => $row['schedule_stop_time'] ?? null, // "HH:MM:SS" | null
    'schedule_duration_minutes' => (int)$row['schedule_duration_minutes'],
    'config_version'            => (int)$row['config_version'],
    'timestamp'                 => date('Y-m-d H:i:s')
]);

// Connection handled by shared database.php
?>
