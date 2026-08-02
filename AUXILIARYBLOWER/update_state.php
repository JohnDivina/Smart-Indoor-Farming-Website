<?php
/*
 * ESP32 Update / Legacy Heartbeat Endpoint — Auxiliary Blower
 *
 * ESP32 POSTs its current state here. Server updates heartbeat fields,
 * runs the schedule checker, and responds with the desired state.
 *
 * Prefer /AUXILIARYBLOWER/api/esp_heartbeat.php for newer firmware.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/esp32_auth.php';
verify_esp32_auth();
include '../database.php';

$raw   = file_get_contents('php://input');
$json  = json_decode($raw, true);
$input = array_merge($_GET, $_POST, $json ?? []);

if (empty($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No input provided']);
    exit;
}

// Accept both naming conventions from ESP32
$esp_fan_state = strtolower(trim($input['esp_fan_state'] ?? $input['state'] ?? $input['fan_state'] ?? 'off'));
$esp_mode      = strtolower(trim($input['esp_mode']      ?? $input['mode']  ?? 'manual'));

if (!in_array($esp_fan_state, ['on', 'off'])) { $esp_fan_state = 'off'; }
if (!in_array($esp_mode, ['manual', 'scheduled', 'auto'])) { $esp_mode = 'manual'; }

// 1. Update heartbeat + last reported state
$stmt = $conn->prepare(
    "UPDATE fan_state SET
        last_heartbeat = NOW(),
        esp_fan_state  = ?,
        esp_mode       = ?,
        last_updated   = NOW()
     WHERE id = 1"
);
$stmt->bind_param('ss', $esp_fan_state, $esp_mode);
$stmt->execute();
$stmt->close();

// 2. Run schedule checker (may flip desired_fan_state)
require_once __DIR__ . '/api/check_fan_schedule.php';

// 3. Fetch desired state and respond
$result = $conn->query("SELECT * FROM fan_state WHERE id = 1");
$row    = $result ? $result->fetch_assoc() : null;

if ($row) {
    echo json_encode([
        'success'                   => true,
        'desired_fan_state'         => $row['desired_fan_state'],
        'mode'                      => $row['mode'],
        'schedule_time'             => $row['schedule_time'],
        'schedule_stop_time'        => $row['schedule_stop_time'] ?? null,
        'schedule_duration_minutes' => (int)$row['schedule_duration_minutes'],
        'timestamp'                 => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode(['success' => true]);
}

$conn->close();
?>
