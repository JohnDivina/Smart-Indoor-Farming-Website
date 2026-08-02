<?php
/*
 * ESP32-ONLY Heartbeat Endpoint — Auxiliary Blower
 *
 * NO SESSION AUTHENTICATION — ESP32 cannot use PHP sessions.
 * ESP32 calls this every 5s (POST, form-encoded or JSON).
 *
 * Fields expected from ESP32:
 *   esp_fan_state  = "on" | "off"
 *   esp_mode       = "manual" | "scheduled" | "auto"
 *   wifi_status    = "connected" | "disconnected"  (optional)
 *
 * Server responds with desired state and schedule settings.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/esp32_auth.php';
verify_esp32_auth();

require_once __DIR__ . '/../../database.php';

// Accept JSON body OR form-encoded
$raw  = file_get_contents('php://input');
$json = json_decode($raw, true);

// Merge all sources: JSON body > POST form > GET
$input = array_merge($_GET, $_POST, $json ?? []);

// Normalise incoming state
$esp_fan_state = strtolower(trim($input['esp_fan_state'] ?? $input['state'] ?? $input['fan_state'] ?? 'off'));
$esp_mode      = strtolower(trim($input['esp_mode']      ?? $input['mode']  ?? 'manual'));

if (!in_array($esp_fan_state, ['on', 'off'])) { $esp_fan_state = 'off'; }
if (!in_array($esp_mode, ['manual', 'scheduled', 'auto'])) { $esp_mode = 'manual'; }

$ack_ver = (int)($input['ack_config_version'] ?? $input['ack_ver'] ?? 0);

// 1. Update heartbeat + last reported state
$stmt = $conn->prepare(
    "UPDATE fan_state SET
        last_heartbeat = NOW(),
        esp_fan_state      = ?,
        esp_mode           = ?,
        ack_config_version = GREATEST(ack_config_version, ?),
        last_updated       = NOW()
     WHERE id = 1"
);
$stmt->bind_param('ssi', $esp_fan_state, $esp_mode, $ack_ver);
$stmt->execute();
$stmt->close();

// 2. Run schedule checker (may flip desired_fan_state and update last_schedule_run)
require_once __DIR__ . '/check_fan_schedule.php';

// 3. Fetch current control row to respond
$result = $conn->query("SELECT * FROM fan_state WHERE id = 1");
$row    = $result ? $result->fetch_assoc() : null;

if (!$row) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Controller row missing']);
    $conn->close();
    exit();
}

echo json_encode([
    'success'                  => true,
    'desired_fan_state'        => $row['desired_fan_state'],          // "on" | "off"
    'mode'                     => $row['mode'],                       // "manual" | "scheduled" | "auto"
    'schedule_time'            => $row['schedule_time'],              // "HH:MM:SS" | null
    'schedule_stop_time'       => $row['schedule_stop_time'] ?? null, // "HH:MM:SS" | null
    'schedule_duration_minutes'=> (int)$row['schedule_duration_minutes'],
    'config_version'           => (int)$row['config_version'],
    'ack_config_version'       => (int)$row['ack_config_version'],
    'timestamp'                => date('Y-m-d H:i:s')
]);

// Connection closed by script end or shared resource
?>
