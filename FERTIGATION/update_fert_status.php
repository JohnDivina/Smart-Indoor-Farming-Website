<?php
/*
 * FERTIGATION/update_fert_status.php
 * Called by the ESP32 Fertigation controller (HTTP POST, every 3-5 seconds).
 * The ESP32 reports its actual pump state, wifi status, and ack_config_version
 * so the website knows the controller has applied the latest configuration.
 *
 * Expected JSON payload:
 * {
 *   "actual_pump_state": "on" | "off",
 *   "wifi_status":       "connected" | "disconnected",
 *   "ack_config_version": <int>,
 *   "last_message":      "<optional descriptive string>"
 * }
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/esp32_auth.php';
verify_esp32_auth();
require_once __DIR__ . '/../database.php';

$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input && !empty($_POST)) {
    $input = $_POST;
}

$response = ['success' => false, 'message' => 'Invalid input'];

if (is_array($input)) {
    $actual_pump_state  = $input['actual_pump_state']  ?? null;
    $wifi_status        = $input['wifi_status']        ?? null;
    $ack_config_version = $input['ack_config_version'] ?? null;
    $last_message       = $input['last_message']       ?? '';

    $errors = [];
    if (!in_array($actual_pump_state, ['on', 'off'], true))        $errors[] = 'actual_pump_state';
    if ($wifi_status === null || trim($wifi_status) === '')         $errors[] = 'wifi_status';
    if ($ack_config_version === null || !is_numeric($ack_config_version) || (int)$ack_config_version < 0)
                                                                    $errors[] = 'ack_config_version';

    if (empty($errors)) {
        try {
            $safe_state   = $conn->real_escape_string($actual_pump_state);
            $safe_wifi    = $conn->real_escape_string(trim($wifi_status));
            $safe_msg     = $conn->real_escape_string(substr((string)$last_message, 0, 255));
            $safe_ack     = (int)$ack_config_version;

            $stmt = $conn->prepare(
                "UPDATE fertigation_control
                 SET actual_pump_state = ?,
                     ack_config_version = ?,
                     last_heartbeat = NOW()
                 WHERE id = 1"
            );
            $stmt->bind_param("si", $safe_state, $safe_ack);

            if ($stmt->execute()) {
                // Optional: mirror actual state into esp_pump_state for legacy UI compat
                $conn->query("UPDATE fertigation_control SET esp_pump_state = '$safe_state' WHERE id = 1");
                $response = [
                    'success' => true,
                    'message' => 'Status updated'
                ];
            } else {
                $response['message'] = 'Database update failed';
            }
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Invalid input on fields: ' . implode(', ', $errors);
    }
} else {
    $response['message'] = 'Invalid JSON payload';
}

echo json_encode($response);
$conn->close();
?>
